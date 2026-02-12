<?php
require_once("../config/config.php");

if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'vendor') {
    header("Location: login.php");
    exit();
}

$vendor_id = $_SESSION['user_id'];

if (isset($_GET['update_status']) && isset($_GET['order_id'])) {
    $order_id = mysqli_real_escape_string($conn, $_GET['order_id']);
    $new_status = mysqli_real_escape_string($conn, $_GET['update_status']);
    
    $allowed_statuses = ['Order Confirmed', 'Packed', 'Shipped', 'Out for Delivery', 'Delivered', 'Cancelled'];
    if (in_array($new_status, $allowed_statuses)) {
        mysqli_query($conn, "UPDATE orders SET order_status='$new_status' WHERE id='$order_id' AND vendor_id='$vendor_id'");
        
        $location = "Vendor Warehouse";
        if ($new_status == 'Shipped') $location = "In Transit";
        if ($new_status == 'Out for Delivery') $location = "Local Hub";
        if ($new_status == 'Delivered') $location = "Delivered to Customer";
        
        mysqli_query($conn, "INSERT INTO order_tracking (order_id, tracking_status, location) VALUES ('$order_id', '$new_status', '$location')");
        
        if ($new_status == 'Delivered') {
            mysqli_query($conn, "UPDATE orders SET payment_status='Paid' WHERE id='$order_id'");
            mysqli_query($conn, "UPDATE payments SET payment_status='Paid' WHERE order_id='$order_id'");
        }
    }
    header("Location: orders.php?success=Order status updated");
    exit();
}

$orders_query = "SELECT o.*, u.username AS customer_name 
                 FROM orders o 
                 JOIN users u ON o.customer_id = u.id 
                 WHERE o.vendor_id='$vendor_id' 
                 ORDER BY o.created_at DESC";
$orders_result = mysqli_query($conn, $orders_query);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Orders - AI Price Negotiator</title>
    <link rel="stylesheet" href="../assests/css/style.css">
</head>
<body>

<div class="navbar">
    <span class="brand">AI Price Negotiator - Vendor Panel</span>
    <ul class="nav-links">
        <li><a href="dashboard.php">Dashboard</a></li>
        <li><a href="add_product.php">Add Product</a></li>
        <li><a href="manage_products.php">Products</a></li>
        <li><a href="orders.php" class="active">Orders</a></li>
        <li><a href="negotiations.php">Negotiations</a></li>
        <li><a href="logout.php">Logout</a></li>
    </ul>
</div>

<div class="container">
    <div class="page-header">
        <h1>Manage Orders</h1>
    </div>

    <?php if (isset($_GET['success'])) { ?>
        <div class="alert alert-success"><?php echo htmlspecialchars($_GET['success']); ?></div>
    <?php } ?>

    <?php if (mysqli_num_rows($orders_result) > 0) { ?>
        <table class="data-table">
            <thead>
                <tr>
                    <th>Order ID</th>
                    <th>Customer</th>
                    <th>Tracking ID</th>
                    <th>Amount</th>
                    <th>Status</th>
                    <th>Payment</th>
                    <th>Date</th>
                    <th>Update Status</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($order = mysqli_fetch_assoc($orders_result)) { ?>
                    <tr>
                        <td>#<?php echo $order['id']; ?></td>
                        <td><?php echo htmlspecialchars($order['customer_name']); ?></td>
                        <td><?php echo $order['tracking_id']; ?></td>
                        <td>Rs. <?php echo number_format($order['total_amount'], 2); ?></td>
                        <td>
                            <?php 
                            $status_class = 'info';
                            if ($order['order_status'] == 'Delivered') $status_class = 'success';
                            elseif ($order['order_status'] == 'Cancelled') $status_class = 'danger';
                            elseif ($order['order_status'] == 'Shipped' || $order['order_status'] == 'Out for Delivery') $status_class = 'warning';
                            ?>
                            <span class="badge badge-<?php echo $status_class; ?>"><?php echo $order['order_status']; ?></span>
                        </td>
                        <td><?php echo $order['payment_status']; ?></td>
                        <td><?php echo date('d M Y', strtotime($order['created_at'])); ?></td>
                        <td>
                            <?php if ($order['order_status'] != 'Delivered' && $order['order_status'] != 'Cancelled') { ?>
                                <select onchange="if(this.value) window.location='orders.php?order_id=<?php echo $order['id']; ?>&update_status='+this.value" style="padding:6px; border:1px solid #d1d5db; border-radius:4px; font-size:12px;">
                                    <option value="">-- Change --</option>
                                    <option value="Packed">Packed</option>
                                    <option value="Shipped">Shipped</option>
                                    <option value="Out for Delivery">Out for Delivery</option>
                                    <option value="Delivered">Delivered</option>
                                    <option value="Cancelled">Cancelled</option>
                                </select>
                            <?php } else { ?>
                                <span style="color:#94a3b8;">--</span>
                            <?php } ?>
                        </td>
                    </tr>
                <?php } ?>
            </tbody>
        </table>
    <?php } else { ?>
        <div class="card">
            <p class="text-center">No orders received yet.</p>
        </div>
    <?php } ?>
</div>

</body>
</html>
