<?php
require_once("../config/config.php");

if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'customer') {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];

$cart_query = "SELECT cc.*, p.product_name, p.price, p.vendor_id, p.stock_quantity 
               FROM customer_carts cc 
               JOIN products p ON cc.product_reference = p.id 
               WHERE cc.customer_reference = '$user_id'";
$cart_result = mysqli_query($conn, $cart_query);

if (mysqli_num_rows($cart_result) == 0) {
    header("Location: cart.php?success=Your cart is empty");
    exit();
}

$vendors = [];
while ($item = mysqli_fetch_assoc($cart_result)) {
    $vendors[$item['vendor_id']][] = $item;
}

foreach ($vendors as $vendor_id => $items) {
    $total_amount = 0;
    
    foreach ($items as $item) {
        $unit_price = ($item['negotiated_price'] > 0) ? $item['negotiated_price'] : $item['price'];
        $total_amount += $unit_price * $item['quantity'];
    }

    $admin_commission = round($total_amount * 0.05, 2);

    $tracking_id = "ORD" . str_pad(rand(1, 99999), 5, "0", STR_PAD_LEFT);

    $order_query = "INSERT INTO orders (customer_id, vendor_id, total_amount, admin_commission, order_status, payment_status, payment_method, tracking_id) 
                    VALUES ('$user_id', '$vendor_id', '$total_amount', '$admin_commission', 'Order Confirmed', 'COD', 'COD', '$tracking_id')";
    mysqli_query($conn, $order_query);
    $order_id = mysqli_insert_id($conn);

    foreach ($items as $item) {
        $unit_price = ($item['negotiated_price'] > 0) ? $item['negotiated_price'] : $item['price'];
        $item_total = $unit_price * $item['quantity'];
        $product_ref = $item['product_reference'];
        $qty = $item['quantity'];

        $item_query = "INSERT INTO order_items (order_id, product_id, quantity, price_per_unit, total_price) 
                       VALUES ('$order_id', '$product_ref', '$qty', '$unit_price', '$item_total')";
        mysqli_query($conn, $item_query);

        mysqli_query($conn, "UPDATE products SET stock_quantity = stock_quantity - $qty WHERE id = '$product_ref'");
    }

    $payment_query = "INSERT INTO payments (order_id, payment_method, amount, payment_status) 
                      VALUES ('$order_id', 'COD', '$total_amount', 'Pending')";
    mysqli_query($conn, $payment_query);

    $tracking_query = "INSERT INTO order_tracking (order_id, tracking_status, location) 
                       VALUES ('$order_id', 'Order Confirmed', 'Processing Center')";
    mysqli_query($conn, $tracking_query);
}

mysqli_query($conn, "DELETE FROM customer_carts WHERE customer_reference = '$user_id'");

header("Location: orders.php?success=Order placed successfully!");
exit();
?>
