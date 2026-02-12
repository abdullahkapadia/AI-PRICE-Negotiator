<?php
require_once("../config/config.php");

if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'vendor') {
    header("Location: login.php");
    exit();
}

$vendor_id = $_SESSION['user_id'];

if (isset($_GET['id'])) {
    $product_id = mysqli_real_escape_string($conn, $_GET['id']);
    mysqli_query($conn, "DELETE FROM products WHERE id='$product_id' AND vendor_id='$vendor_id'");
    header("Location: manage_products.php?success=Product deleted successfully");
    exit();
}

header("Location: manage_products.php");
exit();
?>
