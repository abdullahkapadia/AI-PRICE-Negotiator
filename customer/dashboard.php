<?php
require_once("../config/config.php");

if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'customer') {
    header("Location: login.php?error=Please login first");
    exit();
}

$user_id = $_SESSION['user_id'];
$username = $_SESSION['username'];

$search = "";
$category_filter = "";

if (isset($_GET['search'])) {
    $search = mysqli_real_escape_string($conn, $_GET['search']);
}
if (isset($_GET['category'])) {
    $category_filter = mysqli_real_escape_string($conn, $_GET['category']);
}

$product_query = "SELECT p.*, c.category_name, u.username AS vendor_name 
                  FROM products p 
                  JOIN categories c ON p.category_id = c.id 
                  JOIN users u ON p.vendor_id = u.id 
                  WHERE p.product_status = 'Active' AND p.stock_quantity > 0";

if ($search != "") {
    $product_query .= " AND (p.product_name LIKE '%$search%' OR p.product_description LIKE '%$search%')";
}
if ($category_filter != "") {
    $product_query .= " AND p.category_id = '$category_filter'";
}

$product_query .= " ORDER BY p.created_at DESC";
$product_result = mysqli_query($conn, $product_query);

$cat_query = "SELECT * FROM categories ORDER BY category_name";
$cat_result = mysqli_query($conn, $cat_query);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - AI Price Negotiator</title>
    <link rel="stylesheet" href="../assests/css/style.css">
</head>
<body>

<div class="navbar">
    <span class="brand">AI Price Negotiator</span>
    <ul class="nav-links">
        <li><a href="dashboard.php" class="active">Products</a></li>
        <li><a href="cart.php">Cart</a></li>
        <li><a href="wishlist.php">Wishlist</a></li>
        <li><a href="orders.php">My Orders</a></li>
        <li><a href="logout.php">Logout (<?php echo htmlspecialchars($username); ?>)</a></li>
    </ul>
</div>

<div class="container">
    <div class="page-header">
        <h1>Browse Products</h1>
    </div>

    <!-- Search and Filter -->
    <form method="GET" action="dashboard.php">
        <div class="search-bar">
            <input type="text" name="search" placeholder="Search products..." value="<?php echo htmlspecialchars($search); ?>">
            <select name="category">
                <option value="">All Categories</option>
                <?php while ($cat = mysqli_fetch_assoc($cat_result)) { ?>
                    <option value="<?php echo $cat['id']; ?>" <?php if ($category_filter == $cat['id']) echo 'selected'; ?>>
                        <?php echo htmlspecialchars($cat['category_name']); ?>
                    </option>
                <?php } ?>
            </select>
            <button type="submit" class="btn btn-primary">Search</button>
        </div>
    </form>

    <!-- Product Grid -->
    <div class="product-grid">
        <?php if (mysqli_num_rows($product_result) > 0) { ?>
            <?php while ($product = mysqli_fetch_assoc($product_result)) { ?>
                <div class="product-card">
                    <div class="product-image">
                        <?php if ($product['product_image'] && file_exists("../assests/uploads/" . $product['product_image'])) { ?>
                            <img src="../assests/uploads/<?php echo $product['product_image']; ?>" alt="<?php echo htmlspecialchars($product['product_name']); ?>">
                        <?php } else { ?>
                            <span>No Image</span>
                        <?php } ?>
                    </div>
                    <div class="product-info">
                        <div class="product-name"><?php echo htmlspecialchars($product['product_name']); ?></div>
                        <div class="product-price">Rs. <?php echo number_format($product['price'], 2); ?></div>
                        <div class="product-category"><?php echo htmlspecialchars($cat['category_name'] ?? $product['category_name']); ?> | By: <?php echo htmlspecialchars($product['vendor_name']); ?></div>
                    </div>
                    <div class="product-actions">
                        <a href="product_detail.php?id=<?php echo $product['id']; ?>" class="btn btn-primary btn-sm">View Details</a>
                        <a href="wishlist.php?add=<?php echo $product['id']; ?>" class="btn btn-secondary btn-sm">Wishlist</a>
                    </div>
                </div>
            <?php } ?>
        <?php } else { ?>
            <div class="card" style="grid-column: 1 / -1;">
                <p class="text-center">No products found.</p>
            </div>
        <?php } ?>
    </div>
</div>

</body>
</html>
