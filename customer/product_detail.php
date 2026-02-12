<?php
require_once("../config/config.php");

if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'customer') {
    header("Location: login.php?error=Please login first");
    exit();
}

$user_id = $_SESSION['user_id'];

if (!isset($_GET['id'])) {
    header("Location: dashboard.php");
    exit();
}

$product_id = mysqli_real_escape_string($conn, $_GET['id']);

$query = "SELECT p.*, c.category_name, u.username AS vendor_name, u.id AS vendor_id 
          FROM products p 
          JOIN categories c ON p.category_id = c.id 
          JOIN users u ON p.vendor_id = u.id 
          WHERE p.id = '$product_id'";
$result = mysqli_query($conn, $query);

if (mysqli_num_rows($result) == 0) {
    header("Location: dashboard.php");
    exit();
}

$product = mysqli_fetch_assoc($result);

$review_query = "SELECT r.*, u.username FROM reviews r JOIN users u ON r.customer_id = u.id WHERE r.product_id = '$product_id' ORDER BY r.created_at DESC";
$review_result = mysqli_query($conn, $review_query);

$avg_query = "SELECT AVG(rating) AS avg_rating, COUNT(*) AS total_reviews FROM reviews WHERE product_id = '$product_id'";
$avg_result = mysqli_query($conn, $avg_query);
$avg_data = mysqli_fetch_assoc($avg_result);

if (isset($_POST['add_to_cart'])) {
    $qty = (int)$_POST['quantity'];
    if ($qty < 1) $qty = 1;

    $cart_check = "SELECT * FROM customer_carts WHERE customer_reference = '$user_id' AND product_reference = '$product_id'";
    $cart_check_result = mysqli_query($conn, $cart_check);

    if (mysqli_num_rows($cart_check_result) > 0) {
        $update_cart = "UPDATE customer_carts SET quantity = quantity + $qty WHERE customer_reference = '$user_id' AND product_reference = '$product_id'";
        mysqli_query($conn, $update_cart);
    } else {
        $insert_cart = "INSERT INTO customer_carts (customer_reference, product_reference, quantity) VALUES ('$user_id', '$product_id', '$qty')";
        mysqli_query($conn, $insert_cart);
    }
    $cart_msg = "Product added to cart successfully.";
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($product['product_name']); ?> - AI Price Negotiator</title>
    <link rel="stylesheet" href="../assests/css/style.css">
</head>
<body>

<div class="navbar">
    <span class="brand">AI Price Negotiator</span>
    <ul class="nav-links">
        <li><a href="dashboard.php">Products</a></li>
        <li><a href="cart.php">Cart</a></li>
        <li><a href="wishlist.php">Wishlist</a></li>
        <li><a href="orders.php">My Orders</a></li>
        <li><a href="logout.php">Logout</a></li>
    </ul>
</div>

<div class="container">
    <a href="dashboard.php" class="btn btn-secondary btn-sm mb-20">Back to Products</a>

    <?php if (isset($cart_msg)) { ?>
        <div class="alert alert-success"><?php echo $cart_msg; ?></div>
    <?php } ?>

    <div class="card">
        <div style="display: flex; gap: 30px; flex-wrap: wrap;">
            <!-- Product Image -->
            <div style="flex: 0 0 300px;">
                <?php if ($product['product_image'] && file_exists("../assests/uploads/" . $product['product_image'])) { ?>
                    <img src="../assests/uploads/<?php echo $product['product_image']; ?>" alt="<?php echo htmlspecialchars($product['product_name']); ?>" style="width:100%; border-radius:8px;">
                <?php } else { ?>
                    <div style="width:300px; height:300px; background:#e5e7eb; display:flex; align-items:center; justify-content:center; border-radius:8px; color:#94a3b8;">No Image Available</div>
                <?php } ?>
            </div>
            
            <!-- Product Info -->
            <div style="flex: 1;">
                <h1 style="font-size:28px; color:#1e293b; margin-bottom:10px;"><?php echo htmlspecialchars($product['product_name']); ?></h1>
                <p style="color:#64748b; margin-bottom:10px;">Category: <?php echo htmlspecialchars($product['category_name']); ?> | Vendor: <?php echo htmlspecialchars($product['vendor_name']); ?></p>
                
                <div style="font-size:28px; font-weight:700; color:#059669; margin-bottom:15px;">Rs. <?php echo number_format($product['price'], 2); ?></div>
                
                <p style="margin-bottom:10px;">
                    <strong>Stock:</strong> 
                    <?php if ($product['stock_quantity'] > 0) { ?>
                        <span class="badge badge-success"><?php echo $product['stock_quantity']; ?> available</span>
                    <?php } else { ?>
                        <span class="badge badge-danger">Out of Stock</span>
                    <?php } ?>
                </p>

                <?php if ($avg_data['total_reviews'] > 0) { ?>
                    <p style="margin-bottom:15px;">
                        <strong>Rating:</strong> <?php echo number_format($avg_data['avg_rating'], 1); ?> / 5 
                        (<?php echo $avg_data['total_reviews']; ?> reviews)
                    </p>
                <?php } ?>
                
                <p style="color:#475569; line-height:1.8; margin-bottom:20px;"><?php echo nl2br(htmlspecialchars($product['product_description'])); ?></p>

                <!-- Add to Cart -->
                <?php if ($product['stock_quantity'] > 0) { ?>
                <form method="POST" style="display:flex; gap:10px; align-items:center; margin-bottom:15px;">
                    <input type="number" name="quantity" value="1" min="1" max="<?php echo $product['stock_quantity']; ?>" style="width:70px; padding:8px; border:1px solid #d1d5db; border-radius:6px;">
                    <button type="submit" name="add_to_cart" class="btn btn-primary">Add to Cart</button>
                </form>

                <!-- Negotiate Button -->
                <a href="negotiate.php?product_id=<?php echo $product['id']; ?>" class="btn btn-warning">Negotiate Price</a>
                <?php } ?>
            </div>
        </div>
    </div>

    <!-- Reviews Section -->
    <div class="card mt-20">
        <div class="card-header">Customer Reviews</div>

        <?php if (mysqli_num_rows($review_result) > 0) { ?>
            <?php while ($review = mysqli_fetch_assoc($review_result)) { ?>
                <div style="padding:12px 0; border-bottom:1px solid #e5e7eb;">
                    <strong><?php echo htmlspecialchars($review['username']); ?></strong>
                    <span class="badge badge-info" style="margin-left:10px;">Rating: <?php echo $review['rating']; ?>/5</span>
                    <p style="margin-top:5px; color:#475569;"><?php echo nl2br(htmlspecialchars($review['review_text'])); ?></p>
                    <small style="color:#94a3b8;"><?php echo $review['created_at']; ?></small>
                </div>
            <?php } ?>
        <?php } else { ?>
            <p>No reviews yet for this product.</p>
        <?php } ?>

        <!-- Add Review Form -->
        <div class="mt-20">
            <h3 style="font-size:16px; margin-bottom:10px;">Write a Review</h3>
            <form method="POST" action="add_review.php">
                <input type="hidden" name="product_id" value="<?php echo $product['id']; ?>">
                <div class="form-group">
                    <label>Rating (1-5)</label>
                    <select name="rating" required>
                        <option value="5">5 - Excellent</option>
                        <option value="4">4 - Good</option>
                        <option value="3">3 - Average</option>
                        <option value="2">2 - Below Average</option>
                        <option value="1">1 - Poor</option>
                    </select>
                </div>
                <div class="form-group">
                    <label>Review</label>
                    <textarea name="review_text" placeholder="Write your review here..." required></textarea>
                </div>
                <button type="submit" class="btn btn-primary btn-sm">Submit Review</button>
            </form>
        </div>
    </div>
</div>

</body>
</html>
