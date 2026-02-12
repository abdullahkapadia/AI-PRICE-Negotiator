<?php session_start(); ?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>AI Price Negotiator - Smart E-Commerce Platform</title>
    <link rel="stylesheet" href="assests/css/style.css">
</head>
<body>

<!-- Navbar -->
<div class="navbar">
    <span class="brand">AI Price Negotiator</span>
    <ul class="nav-links">
        <li><a href="customer/login.php">Customer Login</a></li>
        <li><a href="vendor/login.php">Vendor Login</a></li>
        <li><a href="admin/login.php">Admin Login</a></li>
    </ul>
</div>

<!-- Hero Section -->
<div class="hero">
    <h1>AI-Powered Price Negotiation</h1>
    <p>A smart e-commerce platform where customers can negotiate product prices using an AI-driven engine. Get the best deals, every time.</p>
    <div class="hero-buttons">
        <a href="customer/register.php" class="btn btn-primary">Register as Customer</a>
        <a href="vendor/register.php" class="btn btn-success">Register as Vendor</a>
    </div>
</div>

<!-- Features Section -->
<div class="features-section">
    <h2>How It Works</h2>
    <div class="features-grid">
        <div class="feature-card">
            <h3>Browse Products</h3>
            <p>Explore a wide range of products listed by verified vendors across multiple categories.</p>
        </div>
        <div class="feature-card">
            <h3>Negotiate with AI</h3>
            <p>Submit your offer and let our AI engine evaluate it. Receive counter-offers or instant acceptance based on smart pricing rules.</p>
        </div>
        <div class="feature-card">
            <h3>Secure Your Deal</h3>
            <p>Once the AI accepts your price, add the product to your cart at the negotiated rate and place your order.</p>
        </div>
    </div>
</div>

<!-- Info Section -->
<div class="features-section" style="background-color: #fff;">
    <h2>Platform Roles</h2>
    <div class="features-grid">
        <div class="feature-card">
            <h3>Customer</h3>
            <p>Register, browse products, negotiate prices, manage your cart and wishlist, place orders, and track deliveries.</p>
        </div>
        <div class="feature-card">
            <h3>Vendor</h3>
            <p>Register your business, list products with negotiation limits, manage orders, and track sales performance.</p>
        </div>
        <div class="feature-card">
            <h3>Administrator</h3>
            <p>Approve vendors, manage users and categories, monitor negotiations, and oversee platform operations.</p>
        </div>
    </div>
</div>

<!-- Footer -->
<div class="footer">
    <p>AI Price Negotiator - E-Commerce Platform | Project</p>
</div>

</body>
</html>
