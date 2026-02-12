<?php

require_once("../config/config.php");

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    $username = mysqli_real_escape_string($conn, $_POST['username']);
    $email = mysqli_real_escape_string($conn, $_POST['email']);
    $phone = mysqli_real_escape_string($conn, $_POST['phone']);
    $business_name = mysqli_real_escape_string($conn, $_POST['business_name']);
    $gst_number = mysqli_real_escape_string($conn, $_POST['gst_number']);
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];

    if ($password != $confirm_password) {
        header("Location: register.php?error=Passwords do not match");
        exit();
    }

    $check_query = "SELECT * FROM users WHERE email='$email' OR phone='$phone' OR username='$username'";
    $check_result = mysqli_query($conn, $check_query);

    if (mysqli_num_rows($check_result) > 0) {
        header("Location: register.php?error=Username, Email or Phone already exists");
        exit();
    }

    $password_hash = password_hash($password, PASSWORD_DEFAULT);

    $query = "INSERT INTO users (username, email, phone, password_hash, role, business_name, gst_number, approval_status) 
              VALUES ('$username', '$email', '$phone', '$password_hash', 'vendor', '$business_name', '$gst_number', 'Pending')";

    if (mysqli_query($conn, $query)) {
        header("Location: login.php?success=Registration successful. Please wait for admin approval before logging in.");
        exit();
    } else {
        header("Location: register.php?error=Registration failed. Please try again.");
        exit();
    }

} else {
    header("Location: register.php");
    exit();
}
?>
