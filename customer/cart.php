<?php
require_once("../config/config.php");

if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'customer') {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];

if (isset($_GET['remove'])) {
    $cart_id = mysqli_real_escape_string($conn, $_GET['remove']);
    mysqli_query($conn, "DELETE FROM customer_carts WHERE cart_id='$cart_id' AND customer_reference='$user_id'");
    header("Location: cart.php?success=Item removed from cart");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['update_cart'])) {
    if (isset($_POST['quantity']) && is_array($_POST['quantity'])) {
        foreach ($_POST['quantity'] as $cid => $qty) {
            $cid = intval($cid);
            $qty = intval($qty);
            if ($qty < 1) $qty = 1;
            mysqli_query($conn, "UPDATE customer_carts SET quantity=$qty WHERE cart_id=$cid AND customer_reference='$user_id'");
        }
    }
    header("Location: cart.php?success=Cart updated");
    exit();
}

$cart_query = "SELECT cc.*, p.product_name, p.price, p.product_image, p.stock_quantity, p.vendor_id 
               FROM customer_carts cc 
               JOIN products p ON cc.product_reference = p.id 
               WHERE cc.customer_reference = '$user_id'";
$cart_result = mysqli_query($conn, $cart_query);

$total = 0;
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Cart - AI Price Negotiator</title>
    <link rel="stylesheet" href="../assests/css/style.css">
</head>
<body>

<div class="navbar">
    <span class="brand">AI Price Negotiator</span>
    <ul class="nav-links">
        <li><a href="dashboard.php">Products</a></li>
        <li><a href="cart.php" class="active">Cart</a></li>
        <li><a href="wishlist.php">Wishlist</a></li>
        <li><a href="orders.php">My Orders</a></li>
        <li><a href="logout.php">Logout</a></li>
    </ul>
</div>

<div class="container">
    <div class="page-header">
        <h1>My Cart</h1>
    </div>

    <?php if (isset($_GET['success'])) { ?>
        <div class="alert alert-success"><?php echo htmlspecialchars($_GET['success']); ?></div>
    <?php } ?>

    <?php if (mysqli_num_rows($cart_result) > 0) { ?>
        <form method="POST" action="cart.php">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Product</th>
                        <th>Price (Rs.)</th>
                        <th>Quantity</th>
                        <th>Subtotal (Rs.)</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($item = mysqli_fetch_assoc($cart_result)) { 
                        $unit_price = ($item['negotiated_price'] > 0) ? $item['negotiated_price'] : $item['price'];
                        $subtotal = $unit_price * $item['quantity'];
                        $total += $subtotal;
                    ?>
                        <tr>
                            <td>
                                <strong><?php echo htmlspecialchars($item['product_name']); ?></strong>
                                <?php if ($item['negotiated_price'] > 0) { ?>
                                    <br><span class="badge badge-success">Negotiated Price</span>
                                <?php } ?>
                            </td>
                            <td>
                                Rs. <?php echo number_format($unit_price, 2); ?>
                                <?php if ($item['negotiated_price'] > 0) { ?>
                                    <br><small style="text-decoration:line-through; color:#94a3b8;">Rs. <?php echo number_format($item['price'], 2); ?></small>
                                <?php } ?>
                            </td>
                            <td>
                                <input type="number" name="quantity[<?php echo $item['cart_id']; ?>]" value="<?php echo $item['quantity']; ?>" min="1" max="<?php echo $item['stock_quantity']; ?>" style="width:70px; padding:6px; border:1px solid #d1d5db; border-radius:4px;">
                            </td>
                            <td>Rs. <?php echo number_format($subtotal, 2); ?></td>
                            <td>
                                <a href="cart.php?remove=<?php echo $item['cart_id']; ?>" class="btn btn-danger btn-sm">Remove</a>
                            </td>
                        </tr>
                    <?php } ?>
                </tbody>
            </table>

            <div class="card mt-20" style="display:flex; justify-content:space-between; align-items:center;">
                <div>
                    <span style="font-size:20px; font-weight:700;">Total: Rs. <?php echo number_format($total, 2); ?></span>
                </div>
                <div style="display:flex; gap:10px;">
                    <button type="submit" name="update_cart" class="btn btn-secondary">Update Cart</button>
                    <a href="place_order.php" class="btn btn-success">Place Order</a>
                </div>
            </div>
        </form>
    <?php } else { ?>
        <div class="card">
            <p class="text-center">Your cart is empty. <a href="dashboard.php">Browse Products</a></p>
        </div>
    <?php } ?>
</div>

</body>
</html>
