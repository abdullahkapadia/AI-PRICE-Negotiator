<?php
require_once("../config/config.php");

if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'customer') {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];

$orders_query = "SELECT o.*, u.username AS vendor_name 
                 FROM orders o 
                 JOIN users u ON o.vendor_id = u.id 
                 WHERE o.customer_id = '$user_id' 
                 ORDER BY o.created_at DESC";
$orders_result = mysqli_query($conn, $orders_query);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Orders - AI Price Negotiator</title>
    <link rel="stylesheet" href="../assests/css/style.css">
</head>
<body>

<div class="navbar">
    <span class="brand">AI Price Negotiator</span>
    <ul class="nav-links">
        <li><a href="dashboard.php">Products</a></li>
        <li><a href="cart.php">Cart</a></li>
        <li><a href="wishlist.php">Wishlist</a></li>
        <li><a href="orders.php" class="active">My Orders</a></li>
        <li><a href="logout.php">Logout</a></li>
    </ul>
</div>

<div class="container">
    <div class="page-header">
        <h1>My Orders</h1>
    </div>

    <?php if (isset($_GET['success'])) { ?>
        <div class="alert alert-success"><?php echo htmlspecialchars($_GET['success']); ?></div>
    <?php } ?>

    <?php if (mysqli_num_rows($orders_result) > 0) { ?>
        <table class="data-table">
            <thead>
                <tr>
                    <th>Order ID</th>
                    <th>Tracking ID</th>
                    <th>Vendor</th>
                    <th>Total (Rs.)</th>
                    <th>Status</th>
                    <th>Payment</th>
                    <th>Date</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($order = mysqli_fetch_assoc($orders_result)) { ?>
                    <tr>
                        <td>#<?php echo $order['id']; ?></td>
                        <td><?php echo $order['tracking_id']; ?></td>
                        <td><?php echo htmlspecialchars($order['vendor_name']); ?></td>
                        <td>Rs. <?php echo number_format($order['total_amount'], 2); ?></td>
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
                        <td><a href="order_detail.php?id=<?php echo $order['id']; ?>" class="btn btn-primary btn-sm">View</a></td>
                    </tr>
                <?php } ?>
            </tbody>
        </table>
    <?php } else { ?>
        <div class="card">
            <p class="text-center">You have no orders yet. <a href="dashboard.php">Start Shopping</a></p>
        </div>
    <?php } ?>
</div>

</body>
</html>
