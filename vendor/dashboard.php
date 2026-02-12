<?php
require_once("../config/config.php");

if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'vendor') {
    header("Location: login.php");
    exit();
}

$vendor_id = $_SESSION['user_id'];
$business_name = $_SESSION['business_name'];

$product_count_query = "SELECT COUNT(*) AS total FROM products WHERE vendor_id='$vendor_id'";
$product_count = mysqli_fetch_assoc(mysqli_query($conn, $product_count_query))['total'];

$order_count_query = "SELECT COUNT(*) AS total FROM orders WHERE vendor_id='$vendor_id'";
$order_count = mysqli_fetch_assoc(mysqli_query($conn, $order_count_query))['total'];

$negotiation_count_query = "SELECT COUNT(*) AS total FROM negotiations_sessions WHERE vendor_id='$vendor_id' AND status='Active'";
$negotiation_count = mysqli_fetch_assoc(mysqli_query($conn, $negotiation_count_query))['total'];

$revenue_query = "SELECT COALESCE(SUM(total_amount), 0) AS total_revenue FROM orders WHERE vendor_id='$vendor_id' AND order_status != 'Cancelled'";
$total_revenue = mysqli_fetch_assoc(mysqli_query($conn, $revenue_query))['total_revenue'];

$recent_orders_query = "SELECT o.*, u.username AS customer_name 
                        FROM orders o 
                        JOIN users u ON o.customer_id = u.id 
                        WHERE o.vendor_id='$vendor_id' 
                        ORDER BY o.created_at DESC LIMIT 10";
$recent_orders = mysqli_query($conn, $recent_orders_query);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Vendor Dashboard - AI Price Negotiator</title>
    <link rel="stylesheet" href="../assests/css/style.css">
</head>
<body>

<div class="navbar">
    <span class="brand">AI Price Negotiator - Vendor Panel</span>
    <ul class="nav-links">
        <li><a href="dashboard.php" class="active">Dashboard</a></li>
        <li><a href="add_product.php">Add Product</a></li>
        <li><a href="manage_products.php">Products</a></li>
        <li><a href="orders.php">Orders</a></li>
        <li><a href="negotiations.php">Negotiations</a></li>
        <li><a href="logout.php">Logout</a></li>
    </ul>
</div>

<div class="container">
    <div class="page-header">
        <h1>Welcome, <?php echo htmlspecialchars($business_name); ?></h1>
    </div>

    <!-- Stats -->
    <div class="stats-grid">
        <div class="stat-card">
            <div class="stat-number"><?php echo $product_count; ?></div>
            <div class="stat-label">Total Products</div>
        </div>
        <div class="stat-card">
            <div class="stat-number"><?php echo $order_count; ?></div>
            <div class="stat-label">Total Orders</div>
        </div>
        <div class="stat-card">
            <div class="stat-number"><?php echo $negotiation_count; ?></div>
            <div class="stat-label">Active Negotiations</div>
        </div>
        <div class="stat-card">
            <div class="stat-number">Rs. <?php echo number_format($total_revenue, 0); ?></div>
            <div class="stat-label">Total Revenue</div>
        </div>
    </div>

    <!-- Recent Orders -->
    <div class="card">
        <div class="card-header">Recent Orders</div>
        <?php if (mysqli_num_rows($recent_orders) > 0) { ?>
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Order ID</th>
                        <th>Customer</th>
                        <th>Amount</th>
                        <th>Status</th>
                        <th>Date</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($order = mysqli_fetch_assoc($recent_orders)) { ?>
                        <tr>
                            <td>#<?php echo $order['id']; ?></td>
                            <td><?php echo htmlspecialchars($order['customer_name']); ?></td>
                            <td>Rs. <?php echo number_format($order['total_amount'], 2); ?></td>
                            <td><span class="badge badge-info"><?php echo $order['order_status']; ?></span></td>
                            <td><?php echo date('d M Y', strtotime($order['created_at'])); ?></td>
                        </tr>
                    <?php } ?>
                </tbody>
            </table>
        <?php } else { ?>
            <p>No orders received yet.</p>
        <?php } ?>
    </div>
</div>

</body>
</html>
