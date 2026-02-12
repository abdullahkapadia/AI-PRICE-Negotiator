<?php
require_once("../config/config.php");

if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'vendor') {
    header("Location: login.php");
    exit();
}

$vendor_id = $_SESSION['user_id'];

$query = "SELECT ns.*, p.product_name, u.username AS customer_name 
          FROM negotiations_sessions ns 
          JOIN products p ON ns.product_id = p.id 
          JOIN users u ON ns.customer_id = u.id 
          WHERE ns.vendor_id='$vendor_id' 
          ORDER BY ns.created_at DESC";
$result = mysqli_query($conn, $query);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Negotiations - AI Price Negotiator</title>
    <link rel="stylesheet" href="../assests/css/style.css">
</head>
<body>

<div class="navbar">
    <span class="brand">AI Price Negotiator - Vendor Panel</span>
    <ul class="nav-links">
        <li><a href="dashboard.php">Dashboard</a></li>
        <li><a href="add_product.php">Add Product</a></li>
        <li><a href="manage_products.php">Products</a></li>
        <li><a href="orders.php">Orders</a></li>
        <li><a href="negotiations.php" class="active">Negotiations</a></li>
        <li><a href="logout.php">Logout</a></li>
    </ul>
</div>

<div class="container">
    <div class="page-header">
        <h1>Negotiation Sessions</h1>
    </div>

    <?php if (mysqli_num_rows($result) > 0) { ?>
        <table class="data-table">
            <thead>
                <tr>
                    <th>Session ID</th>
                    <th>Product</th>
                    <th>Customer</th>
                    <th>Original Price</th>
                    <th>Final Price</th>
                    <th>Status</th>
                    <th>Date</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($session = mysqli_fetch_assoc($result)) { ?>
                    <tr>
                        <td>#<?php echo $session['id']; ?></td>
                        <td><?php echo htmlspecialchars($session['product_name']); ?></td>
                        <td><?php echo htmlspecialchars($session['customer_name']); ?></td>
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
                            $status_class = 'info';
                            if ($session['status'] == 'Accepted') $status_class = 'success';
                            elseif ($session['status'] == 'Expired' || $session['status'] == 'Rejected') $status_class = 'danger';
                            ?>
                            <span class="badge badge-<?php echo $status_class; ?>"><?php echo $session['status']; ?></span>
                        </td>
                        <td><?php echo date('d M Y, h:i A', strtotime($session['created_at'])); ?></td>
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

</body>
</html>
