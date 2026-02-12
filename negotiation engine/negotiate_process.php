<?php

class NegotiationEngine {

    private $conn;
    private $python_path;
    private $engine_dir;

    public function __construct($db_connection) {
        $this->conn = $db_connection;
        $this->engine_dir = __DIR__;
        
        $this->python_path = $this->detectPython();
    }

    
    private function detectPython() {
        $paths = ['python', 'python3', 'C:/Python311/python.exe', 'C:/Python310/python.exe', 'C:/Python39/python.exe'];
        
        foreach ($paths as $path) {
            $output = shell_exec($path . ' --version 2>&1');
            if ($output && strpos($output, 'Python') !== false) {
                return $path;
            }
        }
        return null;
    }

    
    public function negotiate($customer_id, $product_id, $offer_price, $session_id) {
        $product = $this->getProduct($product_id);
        if (!$product) {
            return ['decision' => 'Error', 'message' => 'Product not found', 'ai_price' => 0];
        }

        $original_price = floatval($product['price']);
        $min_price = floatval($product['min_negotiation_price']);

        if ($offer_price >= $original_price) {
            return [
                'decision' => 'Accepted',
                'ai_price' => $original_price,
                'message'  => 'Your offer matches the listing price. Deal confirmed at Rs. ' . number_format($original_price, 2),
                'confidence' => 1.0,
                'model_type' => 'direct'
            ];
        }

        if ($offer_price < $min_price) {
            $suggested = round($original_price * 0.85, 2);
            if ($suggested < $min_price) $suggested = $min_price;
            return [
                'decision' => 'Rejected',
                'ai_price' => $original_price,
                'message'  => 'Your offer is below the acceptable range. Try offering closer to Rs. ' . number_format($suggested, 2),
                'confidence' => 0.0,
                'model_type' => 'direct'
            ];
        }

        $features = $this->gatherFeatures($customer_id, $product_id, $product, $offer_price, $original_price, $session_id);

        if ($this->python_path) {
            $ml_result = $this->callPythonPredict($features);
            if ($ml_result && $ml_result['success']) {
                return [
                    'decision'   => $ml_result['decision'],
                    'ai_price'   => floatval($ml_result['ai_price']),
                    'message'    => $ml_result['message'],
                    'confidence' => floatval($ml_result['confidence']),
                    'model_type' => $ml_result['model_type']
                ];
            }
        }

        return $this->fallbackDecision($features, $offer_price, $original_price, $min_price);
    }

    
    private function gatherFeatures($customer_id, $product_id, $product, $offer_price, $original_price, $session_id) {
        $offer_percentage = round(($offer_price / $original_price) * 100, 2);

        $r = mysqli_query($this->conn, "SELECT COUNT(*) AS c, COALESCE(SUM(total_amount), 0) AS s FROM orders WHERE customer_id='$customer_id' AND order_status != 'Cancelled'");
        $d = mysqli_fetch_assoc($r);
        $customer_orders = intval($d['c']);
        $customer_spent = floatval($d['s']);

        $product_stock = intval($product['stock_quantity']);

        $product_days = max(1, round((time() - strtotime($product['created_at'])) / 86400));

        $r = mysqli_query($this->conn, "SELECT COUNT(*) AS c FROM order_items WHERE product_id='$product_id'");
        $product_sales = intval(mysqli_fetch_assoc($r)['c']);

        $r = mysqli_query($this->conn, "SELECT COUNT(*) AS c FROM negotiations_sessions WHERE product_id='$product_id' AND status='Active'");
        $active_neg = intval(mysqli_fetch_assoc($r)['c']);

        $r = mysqli_query($this->conn, "SELECT COUNT(*) AS total, SUM(CASE WHEN status='Accepted' THEN 1 ELSE 0 END) AS accepted FROM negotiations_sessions WHERE customer_id='$customer_id'");
        $nd = mysqli_fetch_assoc($r);
        $success_rate = (intval($nd['total']) > 0) ? round(intval($nd['accepted']) / intval($nd['total']), 4) : 0.5;

        $r = mysqli_query($this->conn, "SELECT AVG((original_price - final_price) / original_price * 100) AS avg FROM negotiations_sessions WHERE customer_id='$customer_id' AND status='Accepted' AND final_price > 0");
        $avg_discount = round(floatval(mysqli_fetch_assoc($r)['avg']), 2);

        $r = mysqli_query($this->conn, "SELECT MAX(round_number) AS r FROM negotiation_logs WHERE session_id='$session_id'");
        $round_num = intval(mysqli_fetch_assoc($r)['r']);
        if ($round_num < 1) $round_num = 1;

        $category_id = intval($product['category_id']);

        $min_price_pct = ($original_price > 0) ? round((floatval($product['min_negotiation_price']) / $original_price) * 100, 2) : 80;

        return [
            $offer_percentage,
            $customer_orders,
            $customer_spent,
            $product_stock,
            $product_days,
            $product_sales,
            $active_neg,
            $success_rate,
            $avg_discount,
            $round_num,
            $category_id,
            $original_price,
            $min_price_pct
        ];
    }

    
    private function callPythonPredict($features) {
        $script_path = $this->engine_dir . '/predict.py';
        
        if (!file_exists($script_path)) {
            return null;
        }

        $args = implode(' ', array_map('strval', $features));
        $command = $this->python_path . ' "' . $script_path . '" ' . $args . ' 2>&1';
        
        $output = shell_exec($command);
        
        if ($output) {
            $result = json_decode(trim($output), true);
            if ($result && isset($result['success'])) {
                return $result;
            }
        }
        
        return null;
    }

    
    private function fallbackDecision($features, $offer_price, $original_price, $min_price) {
        $offer_pct = $features[0];
        $customer_orders = $features[1];
        $customer_spent = $features[2];
        $stock = $features[3];
        $days_listed = $features[4];

        $loyalty_bonus = min($customer_orders * 0.5 + $customer_spent / 10000, 5);
        $effective_threshold = 85 - $loyalty_bonus;

        if ($stock < 5) $effective_threshold += 3;
        if ($days_listed > 30) $effective_threshold -= 2;

        if ($offer_pct >= 90) {
            return [
                'decision' => 'Accepted',
                'ai_price' => $offer_price,
                'message'  => 'Your offer of Rs. ' . number_format($offer_price, 2) . ' has been accepted.',
                'confidence' => 0.85,
                'model_type' => 'fallback'
            ];
        }

        if ($offer_pct >= $effective_threshold) {
            $discount = 0.05 + ($loyalty_bonus * 0.01);
            $counter = round($original_price * (1 - $discount), 2);
            if ($counter < $min_price) $counter = $min_price;
            if ($counter <= $offer_price) {
                return [
                    'decision' => 'Accepted',
                    'ai_price' => $offer_price,
                    'message'  => 'Your offer has been accepted at Rs. ' . number_format($offer_price, 2),
                    'confidence' => 0.70,
                    'model_type' => 'fallback'
                ];
            }
            return [
                'decision' => 'Counter',
                'ai_price' => $counter,
                'message'  => 'AI Counter Offer: Rs. ' . number_format($counter, 2),
                'confidence' => 0.60,
                'model_type' => 'fallback'
            ];
        }

        if ($offer_pct >= 60) {
            $counter = round($original_price * 0.90, 2);
            if ($counter < $min_price) $counter = $min_price;
            return [
                'decision' => 'Counter',
                'ai_price' => $counter,
                'message'  => 'Your offer is low. AI Counter: Rs. ' . number_format($counter, 2),
                'confidence' => 0.40,
                'model_type' => 'fallback'
            ];
        }

        return [
            'decision' => 'Rejected',
            'ai_price' => $original_price,
            'message'  => 'Your offer is too low. Please offer above 60% of the listed price.',
            'confidence' => 0.10,
            'model_type' => 'fallback'
        ];
    }

    
    private function getProduct($product_id) {
        $query = "SELECT * FROM products WHERE id='$product_id'";
        $result = mysqli_query($this->conn, $query);
        if (mysqli_num_rows($result) > 0) {
            return mysqli_fetch_assoc($result);
        }
        return null;
    }

    
    public function trainModel() {
        if (!$this->python_path) {
            return ['success' => false, 'error' => 'Python not found on this system.'];
        }

        $export_script = $this->engine_dir . '/export_training_data.php';
        if (file_exists($export_script)) {
            include($export_script);
        }

        $train_script = $this->engine_dir . '/train_model.py';
        if (!file_exists($train_script)) {
            return ['success' => false, 'error' => 'Training script not found.'];
        }

        $command = $this->python_path . ' "' . $train_script . '" 2>&1';
        $output = shell_exec($command);

        if ($output) {
            $result = json_decode(trim($output), true);
            if ($result) {
                return $result;
            }
        }

        return ['success' => false, 'error' => 'Training failed. Output: ' . $output];
    }

    
    public function isPythonAvailable() {
        return $this->python_path !== null;
    }

    
    public function getModelStatus() {
        $model_path = $this->engine_dir . '/trained_model.json';
        
        $status = [
            'python_available' => $this->isPythonAvailable(),
            'python_path' => $this->python_path,
            'model_exists' => file_exists($model_path),
            'model_info' => null
        ];

        if (file_exists($model_path)) {
            $data = json_decode(file_get_contents($model_path), true);
            $status['model_info'] = [
                'type' => $data['type'] ?? 'unknown',
                'trained' => $data['trained'] ?? false,
                'accuracy' => $data['accuracy'] ?? 0,
                'samples' => $data['total_samples'] ?? 0
            ];
        }

        return $status;
    }
}
?>
