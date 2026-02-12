<?php
require_once("../config/config.php");

if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'admin') {
    header("Location: login.php");
    exit();
}

if (isset($_GET['delete'])) {
    $pid = mysqli_real_escape_string($conn, $_GET['delete']);
    mysqli_query($conn, "DELETE FROM products WHERE id='$pid'");
    header("Location: manage_products.php?success=Product removed");
    exit();
}

$products_query = "SELECT p.*, c.category_name, u.username AS vendor_name 
                   FROM products p 
                   JOIN categories c ON p.category_id = c.id 
                   JOIN users u ON p.vendor_id = u.id 
                   ORDER BY p.created_at DESC";
$products_result = mysqli_query($conn, $products_query);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Products - Admin</title>
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
            <li><a href="manage_products.php" class="active">Manage Products</a></li>
            <li><a href="manage_categories.php">Categories</a></li>
            <li><a href="negotiation_logs.php">Negotiation Logs</a></li>
            <li><a href="orders.php">Orders</a></li>
            <li><a href="ml_training.php">ML Training</a></li>
            <li><a href="logout.php">Logout</a></li>
        </ul>
    </div>

    <div class="main-content">
        <div class="page-header">
            <h1>Manage Products</h1>
        </div>

        <?php if (isset($_GET['success'])) { ?>
            <div class="alert alert-success"><?php echo htmlspecialchars($_GET['success']); ?></div>
        <?php } ?>

        <?php if (mysqli_num_rows($products_result) > 0) { ?>
            <table class="data-table">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Product</th>
                        <th>Category</th>
                        <th>Vendor</th>
                        <th>Price</th>
                        <th>Stock</th>
                        <th>Status</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($p = mysqli_fetch_assoc($products_result)) { ?>
                        <tr>
                            <td><?php echo $p['id']; ?></td>
                            <td><?php echo htmlspecialchars($p['product_name']); ?></td>
                            <td><?php echo htmlspecialchars($p['category_name']); ?></td>
                            <td><?php echo htmlspecialchars($p['vendor_name']); ?></td>
                            <td>Rs. <?php echo number_format($p['price'], 2); ?></td>
                            <td><?php echo $p['stock_quantity']; ?></td>
                            <td><span class="badge badge-<?php echo ($p['product_status'] == 'Active') ? 'success' : 'danger'; ?>"><?php echo $p['product_status']; ?></span></td>
                            <td>
                                <a href="manage_products.php?delete=<?php echo $p['id']; ?>" class="btn btn-danger btn-sm" onclick="return confirm('Delete this product?')">Remove</a>
                            </td>
                        </tr>
                    <?php } ?>
                </tbody>
            </table>
        <?php } else { ?>
            <div class="card">
                <p class="text-center">No products in the system.</p>
            </div>
        <?php } ?>
    </div>
</div>

</body>
</html>
