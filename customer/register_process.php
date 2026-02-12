<?php

require_once("../config/config.php");

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    $username = mysqli_real_escape_string($conn, $_POST['username']);
    $email = mysqli_real_escape_string($conn, $_POST['email']);
    $phone = mysqli_real_escape_string($conn, $_POST['phone']);
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];

    $address_line1 = mysqli_real_escape_string($conn, $_POST['address_line1']);
    $address_line2 = mysqli_real_escape_string($conn, $_POST['address_line2']);
    $city = mysqli_real_escape_string($conn, $_POST['city']);
    $state = mysqli_real_escape_string($conn, $_POST['state']);
    $postal_code = mysqli_real_escape_string($conn, $_POST['postal_code']);
    $country = mysqli_real_escape_string($conn, $_POST['country']);

    if ($password != $confirm_password) {
        header("Location: register.php?error=Passwords do not match");
        exit();
    }

    $check_query = "SELECT * FROM users WHERE email='$email' OR phone='$phone'";
    $check_result = mysqli_query($conn, $check_query);

    if (mysqli_num_rows($check_result) > 0) {
        header("Location: register.php?error=Email or Phone already exists");
        exit();
    }

    $password_hash = password_hash($password, PASSWORD_DEFAULT);

    $user_query = "INSERT INTO users (username, email, phone, password_hash, role, approval_status) 
                   VALUES ('$username', '$email', '$phone', '$password_hash', 'customer', 'Approved')";

    if (mysqli_query($conn, $user_query)) {

        $user_id = mysqli_insert_id($conn);

        $address_query = "INSERT INTO customer_addresses 
            (user_id, address_line1, address_line2, city, state, postal_code, country)
            VALUES 
            ('$user_id', '$address_line1', '$address_line2', '$city', '$state', '$postal_code', '$country')";

        mysqli_query($conn, $address_query);

        header("Location: login.php?success=Registration successful. Please login.");
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
