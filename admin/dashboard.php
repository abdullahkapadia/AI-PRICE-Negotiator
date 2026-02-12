<?php
require_once("../config/config.php");

if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'admin') {
    header("Location: login.php");
    exit();
}

$total_customers = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) AS c FROM users WHERE role='customer'"))['c'];
$total_vendors = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) AS c FROM users WHERE role='vendor'"))['c'];
$pending_vendors = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) AS c FROM users WHERE role='vendor' AND approval_status='Pending'"))['c'];
$total_products = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) AS c FROM products"))['c'];
$total_orders = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) AS c FROM orders"))['c'];
$total_revenue = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COALESCE(SUM(total_amount), 0) AS r FROM orders WHERE order_status != 'Cancelled'"))['r'];
$admin_commission = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COALESCE(SUM(admin_commission), 0) AS c FROM orders WHERE order_status != 'Cancelled'"))['c'];
$active_negotiations = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) AS c FROM negotiations_sessions WHERE status='Active'"))['c'];
$total_categories = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) AS c FROM categories"))['c'];

$recent_orders_query = "SELECT o.*, uc.username AS customer_name, uv.username AS vendor_name 
                        FROM orders o 
                        JOIN users uc ON o.customer_id = uc.id 
                        JOIN users uv ON o.vendor_id = uv.id 
                        ORDER BY o.created_at DESC LIMIT 10";
$recent_orders = mysqli_query($conn, $recent_orders_query);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - AI Price Negotiator</title>
    <link rel="stylesheet" href="../assests/css/style.css">
</head>
<body>

<div class="page-wrapper">
    <!-- Sidebar -->
    <div class="sidebar">
        <div class="sidebar-header">Admin Panel</div>
        <ul class="sidebar-menu">
            <li><a href="dashboard.php" class="active">Dashboard</a></li>
            <li><a href="manage_vendors.php">Manage Vendors <?php if ($pending_vendors > 0) echo "($pending_vendors)"; ?></a></li>
            <li><a href="manage_users.php">Manage Users</a></li>
            <li><a href="manage_products.php">Manage Products</a></li>
            <li><a href="manage_categories.php">Categories</a></li>
            <li><a href="negotiation_logs.php">Negotiation Logs</a></li>
            <li><a href="orders.php">Orders</a></li>
            <li><a href="ml_training.php">ML Training</a></li>
            <li><a href="logout.php">Logout</a></li>
        </ul>
    </div>

    <!-- Main -->
    <div class="main-content">
        <div class="page-header">
            <h1>Admin Dashboard</h1>
        </div>

        <div class="stats-grid">
            <div class="stat-card">
                <div class="stat-number"><?php echo $total_customers; ?></div>
                <div class="stat-label">Customers</div>
            </div>
            <div class="stat-card">
                <div class="stat-number"><?php echo $total_vendors; ?></div>
                <div class="stat-label">Vendors</div>
            </div>
            <div class="stat-card" style="border-top-color:#d97706;">
                <div class="stat-number"><?php echo $pending_vendors; ?></div>
                <div class="stat-label">Pending Vendors</div>
            </div>
            <div class="stat-card">
                <div class="stat-number"><?php echo $total_products; ?></div>
                <div class="stat-label">Products</div>
            </div>
            <div class="stat-card">
                <div class="stat-number"><?php echo $total_orders; ?></div>
                <div class="stat-label">Total Orders</div>
            </div>
            <div class="stat-card" style="border-top-color:#059669;">
                <div class="stat-number">Rs. <?php echo number_format($total_revenue, 0); ?></div>
                <div class="stat-label">Total Revenue</div>
            </div>
            <div class="stat-card" style="border-top-color:#7c3aed;">
                <div class="stat-number">Rs. <?php echo number_format($admin_commission, 0); ?></div>
                <div class="stat-label">Admin Commission (5%)</div>
            </div>
            <div class="stat-card">
                <div class="stat-number"><?php echo $active_negotiations; ?></div>
                <div class="stat-label">Active Negotiations</div>
            </div>
            <div class="stat-card">
                <div class="stat-number"><?php echo $total_categories; ?></div>
                <div class="stat-label">Categories</div>
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
                            <th>Vendor</th>
                            <th>Amount</th>
                            <th>Commission (5%)</th>
                            <th>Status</th>
                            <th>Date</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while ($order = mysqli_fetch_assoc($recent_orders)) { ?>
                            <tr>
                                <td>#<?php echo $order['id']; ?></td>
                                <td><?php echo htmlspecialchars($order['customer_name']); ?></td>
                                <td><?php echo htmlspecialchars($order['vendor_name']); ?></td>
                                <td>Rs. <?php echo number_format($order['total_amount'], 2); ?></td>
                                <td>Rs. <?php echo number_format($order['admin_commission'], 2); ?></td>
                                <td><span class="badge badge-info"><?php echo $order['order_status']; ?></span></td>
                                <td><?php echo date('d M Y', strtotime($order['created_at'])); ?></td>
                            </tr>
                        <?php } ?>
                    </tbody>
                </table>
            <?php } else { ?>
                <p>No orders yet.</p>
            <?php } ?>
        </div>
    </div>
</div>

</body>
</html>
