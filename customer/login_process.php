<?php

require_once("../config/config.php");

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    $email_or_phone = mysqli_real_escape_string($conn, $_POST['email_or_phone']);
    $password = $_POST['password'];

    $query = "SELECT * FROM users WHERE (email='$email_or_phone' OR phone='$email_or_phone') AND role='customer'";
    $result = mysqli_query($conn, $query);

    if (mysqli_num_rows($result) == 1) {
        $user = mysqli_fetch_assoc($result);

        if ($user['active_status'] == 0) {
            header("Location: login.php?error=Your account has been deactivated. Contact admin.");
            exit();
        }

        if (password_verify($password, $user['password_hash'])) {
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['username'] = $user['username'];
            $_SESSION['role'] = $user['role'];
            header("Location: dashboard.php");
            exit();
        } else {
            header("Location: login.php?error=Invalid password");
            exit();
        }
    } else {
        header("Location: login.php?error=Account not found");
        exit();
    }

} else {
    header("Location: login.php");
    exit();
}
?>
