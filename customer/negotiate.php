<?php
require_once("../config/config.php");

if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'customer') {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];

if (!isset($_GET['product_id'])) {
    header("Location: dashboard.php");
    exit();
}

$product_id = mysqli_real_escape_string($conn, $_GET['product_id']);

$query = "SELECT p.*, u.id AS vendor_id, u.username AS vendor_name 
          FROM products p JOIN users u ON p.vendor_id = u.id 
          WHERE p.id = '$product_id'";
$result = mysqli_query($conn, $query);

if (mysqli_num_rows($result) == 0) {
    header("Location: dashboard.php");
    exit();
}

$product = mysqli_fetch_assoc($result);

$session_query = "SELECT * FROM negotiations_sessions WHERE customer_id='$user_id' AND product_id='$product_id' AND (status='Active' OR status='Accepted') ORDER BY id DESC LIMIT 1";
$session_result = mysqli_query($conn, $session_query);
$active_session = mysqli_fetch_assoc($session_result);

$message = "";
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['offer_price'])) {
    $offer_price = floatval($_POST['offer_price']);
    $original_price = floatval($product['price']);
    $min_price = floatval($product['min_negotiation_price']);
    $vendor_id = $product['vendor_id'];

    if ($offer_price <= 0) {
        $message = "error|Please enter a valid offer price.";
    } else {
        if (!$active_session || $active_session['status'] == 'Accepted') {
            $expiry = date('Y-m-d H:i:s', strtotime('+1 hour'));
            $create_session = "INSERT INTO negotiations_sessions (customer_id, vendor_id, product_id, original_price, status, expiry_time) 
                               VALUES ('$user_id', '$vendor_id', '$product_id', '$original_price', 'Active', '$expiry')";
            mysqli_query($conn, $create_session);
            $session_id = mysqli_insert_id($conn);
        } else {
            $session_id = $active_session['id'];
        }

        $round_query = "SELECT MAX(round_number) AS max_round FROM negotiation_logs WHERE session_id='$session_id'";
        $round_result = mysqli_query($conn, $round_query);
        $round_data = mysqli_fetch_assoc($round_result);
        $current_round = ($round_data['max_round'] ? $round_data['max_round'] : 0) + 1;

        if ($current_round > 5) {
            $message = "error|Maximum negotiation rounds reached. Start a new session.";
            mysqli_query($conn, "UPDATE negotiations_sessions SET status='Expired' WHERE id='$session_id'");
        } else {
            $log_customer = "INSERT INTO negotiation_logs (session_id, sender_id, offer_source, offered_price, round_number, decision_status) 
                            VALUES ('$session_id', '$user_id', 'Customer', '$offer_price', '$current_round', 'Pending')";
            mysqli_query($conn, $log_customer);

            require_once("../negotiation engine/negotiate_process.php");
            $engine = new NegotiationEngine($conn);
            $result = $engine->negotiate($user_id, $product_id, $offer_price, $session_id);

            $ai_decision = $result['decision'];
            $ai_price = $result['ai_price'];
            $confidence = isset($result['confidence']) ? $result['confidence'] : 0;

            if ($ai_decision == 'Accepted') {
                $message = "success|" . $result['message'];
                mysqli_query($conn, "UPDATE negotiations_sessions SET final_price='$ai_price', status='Accepted' WHERE id='$session_id'");
            } elseif ($ai_decision == 'Counter') {
                $message = "info|" . $result['message'];
            } else {
                $message = "error|" . $result['message'];
            }

            $log_ai = "INSERT INTO negotiation_logs (session_id, sender_id, offer_source, offered_price, round_number, decision_status) 
                       VALUES ('$session_id', NULL, 'AI', '$ai_price', '$current_round', '$ai_decision')";
            mysqli_query($conn, $log_ai);

            $session_query = "SELECT * FROM negotiations_sessions WHERE id='$session_id'";
            $session_result = mysqli_query($conn, $session_query);
            $active_session = mysqli_fetch_assoc($session_result);
        }
    }
}

if (isset($_GET['accept_counter']) && $active_session) {
    $session_id = $active_session['id'];
    $last_ai = "SELECT offered_price FROM negotiation_logs WHERE session_id='$session_id' AND offer_source='AI' AND decision_status='Counter' ORDER BY id DESC LIMIT 1";
    $last_ai_result = mysqli_query($conn, $last_ai);
    if (mysqli_num_rows($last_ai_result) > 0) {
        $last_ai_data = mysqli_fetch_assoc($last_ai_result);
        $final_price = $last_ai_data['offered_price'];
        mysqli_query($conn, "UPDATE negotiations_sessions SET final_price='$final_price', status='Accepted' WHERE id='$session_id'");
        $message = "success|Deal confirmed at Rs. " . number_format($final_price, 2) . "!";
        
        $session_query = "SELECT * FROM negotiations_sessions WHERE id='$session_id'";
        $session_result = mysqli_query($conn, $session_query);
        $active_session = mysqli_fetch_assoc($session_result);
    }
}

if (isset($_GET['add_to_cart_negotiated']) && $active_session && $active_session['status'] == 'Accepted') {
    $final_price = $active_session['final_price'];
    
    $cart_check = "SELECT * FROM customer_carts WHERE customer_reference='$user_id' AND product_reference='$product_id'";
    $cart_result = mysqli_query($conn, $cart_check);
    if (mysqli_num_rows($cart_result) > 0) {
        mysqli_query($conn, "UPDATE customer_carts SET quantity = quantity + 1, negotiated_price='$final_price' WHERE customer_reference='$user_id' AND product_reference='$product_id'");
    } else {
        mysqli_query($conn, "INSERT INTO customer_carts (customer_reference, product_reference, quantity, negotiated_price) VALUES ('$user_id', '$product_id', 1, '$final_price')");
    }
    $message = "success|Product added to cart at negotiated price Rs. " . number_format($final_price, 2);
}

$logs = [];
if ($active_session) {
    $log_query = "SELECT * FROM negotiation_logs WHERE session_id='" . $active_session['id'] . "' ORDER BY round_number ASC, id ASC";
    $log_result = mysqli_query($conn, $log_query);
    while ($log = mysqli_fetch_assoc($log_result)) {
        $logs[] = $log;
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Negotiate Price - AI Price Negotiator</title>
    <link rel="stylesheet" href="../assests/css/style.css">
</head>
<body>

<div class="navbar">
    <span class="brand">AI Price Negotiator</span>
    <ul class="nav-links">
        <li><a href="dashboard.php">Products</a></li>
        <li><a href="cart.php">Cart</a></li>
        <li><a href="orders.php">My Orders</a></li>
        <li><a href="logout.php">Logout</a></li>
    </ul>
</div>

<div class="container">
    <a href="product_detail.php?id=<?php echo $product_id; ?>" class="btn btn-secondary btn-sm mb-20">Back to Product</a>

    <div class="card">
        <div class="card-header">Negotiate Price</div>
        <div style="display:flex; gap:20px; align-items:center; margin-bottom:20px;">
            <div>
                <h2 style="color:#1e293b;"><?php echo htmlspecialchars($product['product_name']); ?></h2>
                <p style="font-size:24px; font-weight:700; color:#059669;">Original Price: Rs. <?php echo number_format($product['price'], 2); ?></p>
                <p style="color:#64748b;">Negotiation is available for this product. Submit your offer below.</p>
            </div>
        </div>

        <?php 
        if ($message != "") {
            $parts = explode("|", $message);
            $type = $parts[0];
            $text = $parts[1];
            echo '<div class="alert alert-' . $type . '">' . $text . '</div>';
        }
        ?>
    </div>

    <!-- Negotiation History -->
    <?php if (count($logs) > 0) { ?>
    <div class="card">
        <div class="card-header">Negotiation History</div>
        <div class="negotiation-box" style="max-width:100%;">
            <?php foreach ($logs as $log) { ?>
                <?php
                    $css_class = "customer-offer";
                    if ($log['offer_source'] == 'AI') {
                        if ($log['decision_status'] == 'Accepted') $css_class = "ai-response";
                        elseif ($log['decision_status'] == 'Rejected') $css_class = "rejected";
                        else $css_class = "ai-response";
                    }
                ?>
                <div class="negotiation-round <?php echo $css_class; ?>">
                    <div class="round-header">Round <?php echo $log['round_number']; ?> - <?php echo $log['offer_source']; ?></div>
                    <div class="round-price">Rs. <?php echo number_format($log['offered_price'], 2); ?></div>
                    <div class="round-status">Status: <span class="badge badge-<?php echo ($log['decision_status'] == 'Accepted' ? 'success' : ($log['decision_status'] == 'Rejected' ? 'danger' : ($log['decision_status'] == 'Counter' ? 'warning' : 'info'))); ?>"><?php echo $log['decision_status']; ?></span></div>
                </div>
            <?php } ?>
        </div>
    </div>
    <?php } ?>

    <!-- Offer Form or Result -->
    <?php if ($active_session && $active_session['status'] == 'Accepted') { ?>
        <div class="card">
            <div class="alert alert-success">
                Deal Confirmed! Final Price: <strong>Rs. <?php echo number_format($active_session['final_price'], 2); ?></strong>
            </div>
            <a href="negotiate.php?product_id=<?php echo $product_id; ?>&add_to_cart_negotiated=1" class="btn btn-success">Add to Cart at Negotiated Price</a>
        </div>
    <?php } elseif (!$active_session || $active_session['status'] == 'Active') { ?>
        <div class="card">
            <div class="card-header">Make Your Offer</div>
            
            <?php
            if ($active_session && count($logs) > 0) {
                $last_log = end($logs);
                if ($last_log['offer_source'] == 'AI' && $last_log['decision_status'] == 'Counter') {
                    echo '<div class="mb-20">';
                    echo '<p>The AI has offered Rs. ' . number_format($last_log['offered_price'], 2) . '. You can accept this offer or make a new one.</p>';
                    echo '<a href="negotiate.php?product_id=' . $product_id . '&accept_counter=1" class="btn btn-success btn-sm mt-10">Accept Counter Offer</a>';
                    echo '</div>';
                }
            }
            ?>

            <form method="POST">
                <div class="form-group">
                    <label>Your Offer Price (Rs.)</label>
                    <input type="number" name="offer_price" step="0.01" min="1" max="<?php echo $product['price']; ?>" placeholder="Enter your offer" required>
                </div>
                <button type="submit" class="btn btn-primary">Submit Offer</button>
            </form>
        </div>
    <?php } else { ?>
        <div class="card">
            <div class="alert alert-warning">This negotiation session has ended. <a href="product_detail.php?id=<?php echo $product_id; ?>">Go back to product</a> to start a new session.</div>
        </div>
    <?php } ?>
</div>

</body>
</html>
