<?php
require_once("../config/config.php");

if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'customer') {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];

if (isset($_GET['add'])) {
    $product_id = mysqli_real_escape_string($conn, $_GET['add']);
    
    $check = "SELECT * FROM wishlists WHERE customer_id='$user_id' AND product_id='$product_id'";
    $check_result = mysqli_query($conn, $check);
    
    if (mysqli_num_rows($check_result) == 0) {
        mysqli_query($conn, "INSERT INTO wishlists (customer_id, product_id) VALUES ('$user_id', '$product_id')");
    }
    header("Location: wishlist.php?success=Product added to wishlist");
    exit();
}

if (isset($_GET['remove'])) {
    $wishlist_id = mysqli_real_escape_string($conn, $_GET['remove']);
    mysqli_query($conn, "DELETE FROM wishlists WHERE id='$wishlist_id' AND customer_id='$user_id'");
    header("Location: wishlist.php?success=Product removed from wishlist");
    exit();
}

$wishlist_query = "SELECT w.*, p.product_name, p.price, p.product_image, p.stock_quantity, p.product_status 
                   FROM wishlists w 
                   JOIN products p ON w.product_id = p.id 
                   WHERE w.customer_id = '$user_id' 
                   ORDER BY w.created_at DESC";
$wishlist_result = mysqli_query($conn, $wishlist_query);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Wishlist - AI Price Negotiator</title>
    <link rel="stylesheet" href="../assests/css/style.css">
</head>
<body>

<div class="navbar">
    <span class="brand">AI Price Negotiator</span>
    <ul class="nav-links">
        <li><a href="dashboard.php">Products</a></li>
        <li><a href="cart.php">Cart</a></li>
        <li><a href="wishlist.php" class="active">Wishlist</a></li>
        <li><a href="orders.php">My Orders</a></li>
        <li><a href="logout.php">Logout</a></li>
    </ul>
</div>

<div class="container">
    <div class="page-header">
        <h1>My Wishlist</h1>
    </div>

    <?php if (isset($_GET['success'])) { ?>
        <div class="alert alert-success"><?php echo htmlspecialchars($_GET['success']); ?></div>
    <?php } ?>

    <?php if (mysqli_num_rows($wishlist_result) > 0) { ?>
        <table class="data-table">
            <thead>
                <tr>
                    <th>Product</th>
                    <th>Price (Rs.)</th>
                    <th>Status</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($item = mysqli_fetch_assoc($wishlist_result)) { ?>
                    <tr>
                        <td><strong><?php echo htmlspecialchars($item['product_name']); ?></strong></td>
                        <td>Rs. <?php echo number_format($item['price'], 2); ?></td>
                        <td>
                            <?php if ($item['stock_quantity'] > 0 && $item['product_status'] == 'Active') { ?>
                                <span class="badge badge-success">In Stock</span>
                            <?php } else { ?>
                                <span class="badge badge-danger">Out of Stock</span>
                            <?php } ?>
                        </td>
                        <td>
                            <a href="product_detail.php?id=<?php echo $item['product_id']; ?>" class="btn btn-primary btn-sm">View</a>
                            <a href="wishlist.php?remove=<?php echo $item['id']; ?>" class="btn btn-danger btn-sm">Remove</a>
                        </td>
                    </tr>
                <?php } ?>
            </tbody>
        </table>
    <?php } else { ?>
        <div class="card">
            <p class="text-center">Your wishlist is empty. <a href="dashboard.php">Browse Products</a></p>
        </div>
    <?php } ?>
</div>

</body>
</html>
