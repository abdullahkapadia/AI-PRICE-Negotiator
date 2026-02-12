<?php
require_once("../config/config.php");

if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'admin') {
    header("Location: login.php");
    exit();
}

$orders_query = "SELECT o.*, uc.username AS customer_name, uv.username AS vendor_name 
                 FROM orders o 
                 JOIN users uc ON o.customer_id = uc.id 
                 JOIN users uv ON o.vendor_id = uv.id 
                 ORDER BY o.created_at DESC";
$orders_result = mysqli_query($conn, $orders_query);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>All Orders - Admin</title>
    <link rel="stylesheet" href="../assests/css/style.css">
</head>
<body>

<div class="page-wrapper">
    <div class="sidebar">
        <div class="sidebar-header">Admin Panel</div>
        <ul class="sidebar-menu">
            <li><a href="dashboard.php">Dashboard</a></li>
            <li><a href="manage_vendors.php">Manage Vendors</a></li>
            <li><a href="manage_users.php">Manage Users</a></li>
            <li><a href="manage_products.php">Manage Products</a></li>
            <li><a href="manage_categories.php">Categories</a></li>
            <li><a href="negotiation_logs.php">Negotiation Logs</a></li>
            <li><a href="orders.php" class="active">Orders</a></li>
            <li><a href="ml_training.php">ML Training</a></li>
            <li><a href="logout.php">Logout</a></li>
        </ul>
    </div>

    <div class="main-content">
        <div class="page-header">
            <h1>All Orders</h1>
        </div>

        <?php if (mysqli_num_rows($orders_result) > 0) { ?>
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Order ID</th>
                        <th>Customer</th>
                        <th>Vendor</th>
                        <th>Tracking ID</th>
                        <th>Amount</th>
                        <th>Commission (5%)</th>
                        <th>Status</th>
                        <th>Payment</th>
                        <th>Date</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($order = mysqli_fetch_assoc($orders_result)) { ?>
                        <tr>
                            <td>#<?php echo $order['id']; ?></td>
                            <td><?php echo htmlspecialchars($order['customer_name']); ?></td>
                            <td><?php echo htmlspecialchars($order['vendor_name']); ?></td>
                            <td><?php echo $order['tracking_id']; ?></td>
                            <td>Rs. <?php echo number_format($order['total_amount'], 2); ?></td>
                            <td>Rs. <?php echo number_format($order['admin_commission'], 2); ?></td>
                            <td>
                                <?php 
                                $status_class = 'info';
                                if ($order['order_status'] == 'Delivered') $status_class = 'success';
                                elseif ($order['order_status'] == 'Cancelled') $status_class = 'danger';
                                elseif ($order['order_status'] == 'Shipped') $status_class = 'warning';
                                ?>
                                <span class="badge badge-<?php echo $status_class; ?>"><?php echo $order['order_status']; ?></span>
                            </td>
                            <td><?php echo $order['payment_status']; ?> (<?php echo $order['payment_method']; ?>)</td>
                            <td><?php echo date('d M Y', strtotime($order['created_at'])); ?></td>
                        </tr>
                    <?php } ?>
                </tbody>
            </table>
        <?php } else { ?>
            <div class="card">
                <p class="text-center">No orders in the system.</p>
            </div>
        <?php } ?>
    </div>
</div>

</body>
</html>
