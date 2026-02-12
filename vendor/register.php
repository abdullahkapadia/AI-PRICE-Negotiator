<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Vendor Registration - AI Price Negotiator</title>
    <link rel="stylesheet" href="../assests/css/style.css">
</head>
<body>

<div class="navbar">
    <span class="brand">AI Price Negotiator</span>
    <ul class="nav-links">
        <li><a href="login.php">Login</a></li>
        <li><a href="../index.php">Home</a></li>
    </ul>
</div>

<div class="form-container">
    <h2>Vendor Registration</h2>

    <?php
    if (isset($_GET['error'])) {
        echo '<div class="alert alert-error">' . htmlspecialchars($_GET['error']) . '</div>';
    }
    if (isset($_GET['success'])) {
        echo '<div class="alert alert-success">' . htmlspecialchars($_GET['success']) . '</div>';
    }
    ?>

    <form method="POST" action="register_process.php">
        <div class="form-group">
            <label>Username</label>
            <input type="text" name="username" required>
        </div>

        <div class="form-group">
            <label>Email</label>
            <input type="email" name="email" required>
        </div>

        <div class="form-group">
            <label>Phone</label>
            <input type="text" name="phone" required maxlength="15">
        </div>

        <div class="form-group">
            <label>Business Name</label>
            <input type="text" name="business_name" required>
        </div>

        <div class="form-group">
            <label>GST Number</label>
            <input type="text" name="gst_number" required>
        </div>

        <div class="form-group">
            <label>Password</label>
            <input type="password" name="password" required>
        </div>

        <div class="form-group">
            <label>Confirm Password</label>
            <input type="password" name="confirm_password" required>
        </div>

        <button type="submit" class="btn btn-success btn-block mt-10">Register as Vendor</button>
    </form>

    <p class="text-center mt-20">Already registered? <a href="login.php">Login Here</a></p>
</div>

</body>
</html>
