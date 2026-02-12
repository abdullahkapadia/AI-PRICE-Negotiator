<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Login - AI Price Negotiator</title>
    <link rel="stylesheet" href="../assests/css/style.css">
</head>
<body>

<div class="navbar">
    <span class="brand">AI Price Negotiator - Admin</span>
    <ul class="nav-links">
        <li><a href="../index.php">Home</a></li>
    </ul>
</div>

<div class="form-container">
    <h2>Admin Login</h2>

    <?php
    if (isset($_GET['error'])) {
        echo '<div class="alert alert-error">' . htmlspecialchars($_GET['error']) . '</div>';
    }
    ?>

    <form action="login_process.php" method="POST">
        <div class="form-group">
            <label>Email</label>
            <input type="email" name="email" required>
        </div>

        <div class="form-group">
            <label>Password</label>
            <input type="password" name="password" required>
        </div>

        <button type="submit" class="btn btn-primary btn-block">Login</button>
    </form>
</div>

</body>
</html>
