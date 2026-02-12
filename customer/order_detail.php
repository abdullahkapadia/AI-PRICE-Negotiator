<?php
require_once("../config/config.php");

if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'customer') {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];

if (!isset($_GET['id'])) {
    header("Location: orders.php");
    exit();
}

$order_id = mysqli_real_escape_string($conn, $_GET['id']);

$order_query = "SELECT o.*, u.username AS vendor_name 
                FROM orders o 
                JOIN users u ON o.vendor_id = u.id 
                WHERE o.id = '$order_id' AND o.customer_id = '$user_id'";
$order_result = mysqli_query($conn, $order_query);

if (mysqli_num_rows($order_result) == 0) {
    header("Location: orders.php");
    exit();
}

$order = mysqli_fetch_assoc($order_result);

$items_query = "SELECT oi.*, p.product_name, p.product_image 
                FROM order_items oi 
                JOIN products p ON oi.product_id = p.id 
                WHERE oi.order_id = '$order_id'";
$items_result = mysqli_query($conn, $items_query);

$tracking_query = "SELECT * FROM order_tracking WHERE order_id = '$order_id' ORDER BY updated_at ASC";
$tracking_result = mysqli_query($conn, $tracking_query);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Order #<?php echo $order_id; ?> - AI Price Negotiator</title>
    <link rel="stylesheet" href="../assests/css/style.css">
</head>
<body>

<div class="navbar">
    <span class="brand">AI Price Negotiator</span>
    <ul class="nav-links">
        <li><a href="dashboard.php">Products</a></li>
        <li><a href="cart.php">Cart</a></li>
        <li><a href="orders.php" class="active">My Orders</a></li>
        <li><a href="logout.php">Logout</a></li>
    </ul>
</div>

<div class="container">
    <a href="orders.php" class="btn btn-secondary btn-sm mb-20">Back to Orders</a>

    <div class="card">
        <div class="card-header">Order #<?php echo $order_id; ?></div>
        <div style="display:grid; grid-template-columns:1fr 1fr; gap:15px;">
            <p><strong>Tracking ID:</strong> <?php echo $order['tracking_id']; ?></p>
            <p><strong>Vendor:</strong> <?php echo htmlspecialchars($order['vendor_name']); ?></p>
            <p><strong>Order Date:</strong> <?php echo date('d M Y, h:i A', strtotime($order['created_at'])); ?></p>
            <p><strong>Payment Method:</strong> <?php echo $order['payment_method']; ?></p>
            <p><strong>Status:</strong> <span class="badge badge-info"><?php echo $order['order_status']; ?></span></p>
            <p><strong>Payment Status:</strong> <?php echo $order['payment_status']; ?></p>
            <p><strong>Total Amount:</strong> <span style="font-size:20px; font-weight:700; color:#059669;">Rs. <?php echo number_format($order['total_amount'], 2); ?></span></p>
        </div>
    </div>

    <!-- Order Items -->
    <div class="card">
        <div class="card-header">Order Items</div>
        <table class="data-table">
            <thead>
                <tr>
                    <th>Product</th>
                    <th>Price (Rs.)</th>
                    <th>Quantity</th>
                    <th>Total (Rs.)</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($item = mysqli_fetch_assoc($items_result)) { ?>
                    <tr>
                        <td><?php echo htmlspecialchars($item['product_name']); ?></td>
                        <td>Rs. <?php echo number_format($item['price_per_unit'], 2); ?></td>
                        <td><?php echo $item['quantity']; ?></td>
                        <td>Rs. <?php echo number_format($item['total_price'], 2); ?></td>
                    </tr>
                <?php } ?>
            </tbody>
        </table>
    </div>

    <!-- Tracking History -->
    <div class="card">
        <div class="card-header">Order Tracking</div>
        <?php if (mysqli_num_rows($tracking_result) > 0) { ?>
            <div style="padding:10px 0;">
                <?php while ($track = mysqli_fetch_assoc($tracking_result)) { ?>
                    <div style="padding:10px 0; border-left:3px solid #3b82f6; padding-left:15px; margin-bottom:10px;">
                        <strong><?php echo $track['tracking_status']; ?></strong>
                        <?php if ($track['location']) { ?>
                            <span style="color:#64748b;"> - <?php echo htmlspecialchars($track['location']); ?></span>
                        <?php } ?>
                        <br><small style="color:#94a3b8;"><?php echo date('d M Y, h:i A', strtotime($track['updated_at'])); ?></small>
                    </div>
                <?php } ?>
            </div>
        <?php } else { ?>
            <p>No tracking information available yet.</p>
        <?php } ?>
    </div>
</div>

</body>
</html>
