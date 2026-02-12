<?php

global $conn;
if (!isset($conn) || $conn === null) {
    require_once(__DIR__ . "/../config/config.php");
}

$csv_path = __DIR__ . "/training_data.csv";
$file = fopen($csv_path, "w");

$header = [
    'session_id',
    'offer_percentage',
    'customer_total_orders',
    'customer_total_spent',
    'product_stock',
    'product_days_listed',
    'product_total_sales',
    'active_negotiations',
    'customer_success_rate',
    'customer_avg_discount',
    'round_number',
    'category_id',
    'original_price',
    'min_price_percentage',
    'decision'
];
fputcsv($file, $header);

$query = "SELECT nl.*, ns.customer_id, ns.product_id, ns.original_price, ns.status AS session_status,
                 p.stock_quantity, p.category_id, p.min_negotiation_price, p.created_at AS product_created
          FROM negotiation_logs nl
          JOIN negotiations_sessions ns ON nl.session_id = ns.id
          JOIN products p ON ns.product_id = p.id
          WHERE nl.offer_source = 'Customer'
          ORDER BY nl.id";

$result = mysqli_query($conn, $query);

while ($row = mysqli_fetch_assoc($result)) {
    $customer_id = $row['customer_id'];
    $product_id = $row['product_id'];
    $original_price = floatval($row['original_price']);
    $offer_price = floatval($row['offered_price']);

    $offer_percentage = ($original_price > 0) ? round(($offer_price / $original_price) * 100, 2) : 0;

    $r = mysqli_query($conn, "SELECT COUNT(*) AS c, COALESCE(SUM(total_amount), 0) AS s FROM orders WHERE customer_id='$customer_id' AND order_status != 'Cancelled'");
    $d = mysqli_fetch_assoc($r);
    $customer_total_orders = intval($d['c']);
    $customer_total_spent = floatval($d['s']);

    $product_stock = intval($row['stock_quantity']);
    $product_created = strtotime($row['product_created']);
    $product_days_listed = max(1, round((time() - $product_created) / 86400));

    $r = mysqli_query($conn, "SELECT COUNT(*) AS c FROM order_items WHERE product_id='$product_id'");
    $product_total_sales = intval(mysqli_fetch_assoc($r)['c']);

    $r = mysqli_query($conn, "SELECT COUNT(*) AS c FROM negotiations_sessions WHERE product_id='$product_id' AND status='Active'");
    $active_negotiations = intval(mysqli_fetch_assoc($r)['c']);

    $r = mysqli_query($conn, "SELECT COUNT(*) AS total, SUM(CASE WHEN status='Accepted' THEN 1 ELSE 0 END) AS accepted FROM negotiations_sessions WHERE customer_id='$customer_id'");
    $nd = mysqli_fetch_assoc($r);
    $customer_success_rate = (intval($nd['total']) > 0) ? round(intval($nd['accepted']) / intval($nd['total']), 4) : 0.5;

    $r = mysqli_query($conn, "SELECT AVG((original_price - final_price) / original_price * 100) AS avg_disc FROM negotiations_sessions WHERE customer_id='$customer_id' AND status='Accepted' AND final_price > 0");
    $customer_avg_discount = round(floatval(mysqli_fetch_assoc($r)['avg_disc']), 2);

    $min_price_pct = ($original_price > 0) ? round((floatval($row['min_negotiation_price']) / $original_price) * 100, 2) : 80;

    $r = mysqli_query($conn, "SELECT decision_status FROM negotiation_logs WHERE session_id='" . $row['session_id'] . "' AND round_number='" . $row['round_number'] . "' AND offer_source='AI' LIMIT 1");
    $ai_log = mysqli_fetch_assoc($r);
    $decision = ($ai_log) ? $ai_log['decision_status'] : 'Pending';

    $decision_numeric = 0;
    if ($decision == 'Accepted') $decision_numeric = 1;
    elseif ($decision == 'Rejected') $decision_numeric = -1;

    fputcsv($file, [
        $row['session_id'],
        $offer_percentage,
        $customer_total_orders,
        $customer_total_spent,
        $product_stock,
        $product_days_listed,
        $product_total_sales,
        $active_negotiations,
        $customer_success_rate,
        $customer_avg_discount,
        $row['round_number'],
        $row['category_id'],
        $original_price,
        $min_price_pct,
        $decision_numeric
    ]);
}

fclose($file);

$r = mysqli_query($conn, "SELECT COUNT(*) AS c FROM negotiation_logs WHERE offer_source='Customer'");
$total = intval(mysqli_fetch_assoc($r)['c']);

if (isset($_GET['from_admin'])) {
    echo json_encode(['success' => true, 'rows' => $total, 'file' => $csv_path]);
} else {
    echo "Training data exported successfully. Total rows: $total\n";
    echo "File: $csv_path\n";
}
?>
