<?php
require_once("../config/config.php");

if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'vendor') {
    header("Location: login.php");
    exit();
}

$vendor_id = $_SESSION['user_id'];

$cat_query = "SELECT * FROM categories ORDER BY category_name";
$cat_result = mysqli_query($conn, $cat_query);

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $product_name = mysqli_real_escape_string($conn, $_POST['product_name']);
    $product_description = mysqli_real_escape_string($conn, $_POST['product_description']);
    $category_id = mysqli_real_escape_string($conn, $_POST['category_id']);
    $price = floatval($_POST['price']);
    $min_negotiation_price = floatval($_POST['min_negotiation_price']);
    $stock_quantity = (int)$_POST['stock_quantity'];

    if ($min_negotiation_price > $price) {
        $error = "Minimum negotiation price cannot be greater than the product price.";
    } else {
        $product_image = "";
        if (isset($_FILES['product_image']) && $_FILES['product_image']['error'] == 0) {
            $upload_dir = "../assests/uploads/";
            if (!is_dir($upload_dir)) {
                mkdir($upload_dir, 0777, true);
            }
            $file_name = time() . "_" . basename($_FILES['product_image']['name']);
            $target_path = $upload_dir . $file_name;

            $allowed = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
            $ext = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));

            if (in_array($ext, $allowed)) {
                if (move_uploaded_file($_FILES['product_image']['tmp_name'], $target_path)) {
                    $product_image = $file_name;
                }
            }
        }

        $query = "INSERT INTO products (vendor_id, category_id, product_name, product_description, price, min_negotiation_price, stock_quantity, product_image, product_status) 
                  VALUES ('$vendor_id', '$category_id', '$product_name', '$product_description', '$price', '$min_negotiation_price', '$stock_quantity', '$product_image', 'Active')";

        if (mysqli_query($conn, $query)) {
            header("Location: manage_products.php?success=Product added successfully");
            exit();
        } else {
            $error = "Failed to add product. Please try again.";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add Product - AI Price Negotiator</title>
    <link rel="stylesheet" href="../assests/css/style.css">
</head>
<body>

<div class="navbar">
    <span class="brand">AI Price Negotiator - Vendor Panel</span>
    <ul class="nav-links">
        <li><a href="dashboard.php">Dashboard</a></li>
        <li><a href="add_product.php" class="active">Add Product</a></li>
        <li><a href="manage_products.php">Products</a></li>
        <li><a href="orders.php">Orders</a></li>
        <li><a href="negotiations.php">Negotiations</a></li>
        <li><a href="logout.php">Logout</a></li>
    </ul>
</div>

<div class="form-container" style="max-width:600px;">
    <h2>Add New Product</h2>

    <?php if (isset($error)) { ?>
        <div class="alert alert-error"><?php echo $error; ?></div>
    <?php } ?>

    <form method="POST" enctype="multipart/form-data">
        <div class="form-group">
            <label>Product Name</label>
            <input type="text" name="product_name" required>
        </div>

        <div class="form-group">
            <label>Description</label>
            <textarea name="product_description" rows="4" required></textarea>
        </div>

        <div class="form-group">
            <label>Category</label>
            <select name="category_id" required>
                <option value="">Select Category</option>
                <?php while ($cat = mysqli_fetch_assoc($cat_result)) { ?>
                    <option value="<?php echo $cat['id']; ?>"><?php echo htmlspecialchars($cat['category_name']); ?></option>
                <?php } ?>
            </select>
        </div>

        <div class="form-group">
            <label>Price (Rs.)</label>
            <input type="number" name="price" step="0.01" min="1" required>
        </div>

        <div class="form-group">
            <label>Minimum Negotiation Price (Rs.)</label>
            <input type="number" name="min_negotiation_price" step="0.01" min="1" required>
            <small style="color:#64748b;">This is the lowest price you will accept during negotiation.</small>
        </div>

        <div class="form-group">
            <label>Stock Quantity</label>
            <input type="number" name="stock_quantity" min="0" required>
        </div>

        <div class="form-group">
            <label>Product Image</label>
            <input type="file" name="product_image" accept="image/*">
        </div>

        <button type="submit" class="btn btn-success btn-block mt-10">Add Product</button>
    </form>
</div>

</body>
</html>
