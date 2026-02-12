<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Customer Registration - AI Price Negotiator</title>
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
    <h2>Customer Registration</h2>

    <?php
    if (isset($_GET['error'])) {
        echo '<div class="alert alert-error">' . htmlspecialchars($_GET['error']) . '</div>';
    }
    if (isset($_GET['success'])) {
        echo '<div class="alert alert-success">' . htmlspecialchars($_GET['success']) . '</div>';
    }
    ?>

    <form method="POST" action="register_process.php">
        <input type="hidden" name="role" value="customer">

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
            <label>Password</label>
            <input type="password" name="password" required>
        </div>

        <div class="form-group">
            <label>Confirm Password</label>
            <input type="password" name="confirm_password" required>
        </div>

        <h3 class="text-center mt-20 mb-10" style="font-size:18px; color:#1e293b;">Address Details</h3>

        <div class="form-group">
            <label>Address Line 1</label>
            <input type="text" name="address_line1" required>
        </div>

        <div class="form-group">
            <label>Address Line 2</label>
            <input type="text" name="address_line2">
        </div>

        <div class="form-group">
            <label>City</label>
            <input type="text" name="city" required>
        </div>

        <div class="form-group">
            <label>State</label>
            <input type="text" name="state" required>
        </div>

        <div class="form-group">
            <label>Postal Code</label>
            <input type="text" name="postal_code" required>
        </div>

        <div class="form-group">
            <label>Country</label>
            <input type="text" name="country" required>
        </div>

        <button type="submit" class="btn btn-primary btn-block mt-10">Register</button>
    </form>

    <p class="text-center mt-20">Already have an account? <a href="login.php">Login Here</a></p>
</div>

</body>
</html>
