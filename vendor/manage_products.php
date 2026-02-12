<?php
require_once("../config/config.php");

if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'vendor') {
    header("Location: login.php");
    exit();
}

$vendor_id = $_SESSION['user_id'];

$query = "SELECT p.*, c.category_name 
          FROM products p 
          JOIN categories c ON p.category_id = c.id 
          WHERE p.vendor_id = '$vendor_id' 
          ORDER BY p.created_at DESC";
$result = mysqli_query($conn, $query);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Products - AI Price Negotiator</title>
    <link rel="stylesheet" href="../assests/css/style.css">
</head>
<body>

<div class="navbar">
    <span class="brand">AI Price Negotiator - Vendor Panel</span>
    <ul class="nav-links">
        <li><a href="dashboard.php">Dashboard</a></li>
        <li><a href="add_product.php">Add Product</a></li>
        <li><a href="manage_products.php" class="active">Products</a></li>
        <li><a href="orders.php">Orders</a></li>
        <li><a href="negotiations.php">Negotiations</a></li>
        <li><a href="logout.php">Logout</a></li>
    </ul>
</div>

<div class="container">
    <div class="page-header">
        <h1>Manage Products</h1>
        <a href="add_product.php" class="btn btn-success">Add New Product</a>
    </div>

    <?php if (isset($_GET['success'])) { ?>
        <div class="alert alert-success"><?php echo htmlspecialchars($_GET['success']); ?></div>
    <?php } ?>

    <?php if (mysqli_num_rows($result) > 0) { ?>
        <table class="data-table">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Product Name</th>
                    <th>Category</th>
                    <th>Price (Rs.)</th>
                    <th>Min Neg. Price</th>
                    <th>Stock</th>
                    <th>Status</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($product = mysqli_fetch_assoc($result)) { ?>
                    <tr>
                        <td><?php echo $product['id']; ?></td>
                        <td><?php echo htmlspecialchars($product['product_name']); ?></td>
                        <td><?php echo htmlspecialchars($product['category_name']); ?></td>
                        <td>Rs. <?php echo number_format($product['price'], 2); ?></td>
                        <td>Rs. <?php echo number_format($product['min_negotiation_price'], 2); ?></td>
                        <td><?php echo $product['stock_quantity']; ?></td>
                        <td><span class="badge badge-<?php echo ($product['product_status'] == 'Active') ? 'success' : 'danger'; ?>"><?php echo $product['product_status']; ?></span></td>
                        <td>
                            <a href="edit_product.php?id=<?php echo $product['id']; ?>" class="btn btn-primary btn-sm">Edit</a>
                            <a href="delete_product.php?id=<?php echo $product['id']; ?>" class="btn btn-danger btn-sm" onclick="return confirm('Are you sure you want to delete this product?')">Delete</a>
                        </td>
                    </tr>
                <?php } ?>
            </tbody>
        </table>
    <?php } else { ?>
        <div class="card">
            <p class="text-center">No products added yet. <a href="add_product.php">Add your first product</a></p>
        </div>
    <?php } ?>
</div>

</body>
</html>
