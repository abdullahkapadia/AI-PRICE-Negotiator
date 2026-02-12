<?php
require_once("../config/config.php");

if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'customer') {
    header("Location: login.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $user_id = $_SESSION['user_id'];
    $product_id = mysqli_real_escape_string($conn, $_POST['product_id']);
    $rating = (int)$_POST['rating'];
    $review_text = mysqli_real_escape_string($conn, $_POST['review_text']);

    if ($rating < 1 || $rating > 5) {
        header("Location: product_detail.php?id=$product_id&error=Invalid rating");
        exit();
    }

    $check = "SELECT * FROM reviews WHERE customer_id='$user_id' AND product_id='$product_id'";
    $check_result = mysqli_query($conn, $check);

    if (mysqli_num_rows($check_result) > 0) {
        $update = "UPDATE reviews SET rating='$rating', review_text='$review_text' WHERE customer_id='$user_id' AND product_id='$product_id'";
        mysqli_query($conn, $update);
    } else {
        $insert = "INSERT INTO reviews (customer_id, product_id, rating, review_text) VALUES ('$user_id', '$product_id', '$rating', '$review_text')";
        mysqli_query($conn, $insert);
    }

    header("Location: product_detail.php?id=$product_id");
    exit();
}

header("Location: dashboard.php");
exit();
?>
