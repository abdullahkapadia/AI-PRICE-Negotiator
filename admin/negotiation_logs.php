<?php
require_once("../config/config.php");

if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'admin') {
    header("Location: login.php");
    exit();
}

$query = "SELECT ns.*, p.product_name, uc.username AS customer_name, uv.username AS vendor_name 
          FROM negotiations_sessions ns 
          JOIN products p ON ns.product_id = p.id 
          JOIN users uc ON ns.customer_id = uc.id 
          JOIN users uv ON ns.vendor_id = uv.id 
          ORDER BY ns.created_at DESC";
$result = mysqli_query($conn, $query);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Negotiation Logs - Admin</title>
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
            <li><a href="negotiation_logs.php" class="active">Negotiation Logs</a></li>
            <li><a href="orders.php">Orders</a></li>
            <li><a href="ml_training.php">ML Training</a></li>
            <li><a href="logout.php">Logout</a></li>
        </ul>
    </div>

    <div class="main-content">
        <div class="page-header">
            <h1>Negotiation Logs</h1>
        </div>

        <?php if (mysqli_num_rows($result) > 0) { ?>
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Session ID</th>
                        <th>Product</th>
                        <th>Customer</th>
                        <th>Vendor</th>
                        <th>Original Price</th>
                        <th>Final Price</th>
                        <th>Status</th>
                        <th>Date</th>
                        <th>Details</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($session = mysqli_fetch_assoc($result)) { ?>
                        <tr>
                            <td>#<?php echo $session['id']; ?></td>
                            <td><?php echo htmlspecialchars($session['product_name']); ?></td>
                            <td><?php echo htmlspecialchars($session['customer_name']); ?></td>
                            <td><?php echo htmlspecialchars($session['vendor_name']); ?></td>
                            <td>Rs. <?php echo number_format($session['original_price'], 2); ?></td>
                            <td>
                                <?php if ($session['final_price']) { ?>
                                    Rs. <?php echo number_format($session['final_price'], 2); ?>
                                <?php } else { ?>
                                    --
                                <?php } ?>
                            </td>
                            <td>
                                <?php 
                                $s_class = 'info';
                                if ($session['status'] == 'Accepted') $s_class = 'success';
                                elseif ($session['status'] == 'Expired' || $session['status'] == 'Rejected') $s_class = 'danger';
                                ?>
                                <span class="badge badge-<?php echo $s_class; ?>"><?php echo $session['status']; ?></span>
                            </td>
                            <td><?php echo date('d M Y, h:i A', strtotime($session['created_at'])); ?></td>
                            <td>
                                <?php
                                $log_query = "SELECT * FROM negotiation_logs WHERE session_id='" . $session['id'] . "' ORDER BY round_number, id";
                                $log_result = mysqli_query($conn, $log_query);
                                $rounds = mysqli_num_rows($log_result);
                                ?>
                                <span class="badge badge-secondary"><?php echo $rounds; ?> entries</span>
                            </td>
                        </tr>
                    <?php } ?>
                </tbody>
            </table>
        <?php } else { ?>
            <div class="card">
                <p class="text-center">No negotiation sessions found.</p>
            </div>
        <?php } ?>
    </div>
</div>

</body>
</html>
