<?php
require_once("../config/config.php");

if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'vendor') {
    header("Location: login.php");
    exit();
}

$vendor_id = $_SESSION['user_id'];

if (!isset($_GET['id'])) {
    header("Location: manage_products.php");
    exit();
}

$product_id = mysqli_real_escape_string($conn, $_GET['id']);

$query = "SELECT * FROM products WHERE id='$product_id' AND vendor_id='$vendor_id'";
$result = mysqli_query($conn, $query);

if (mysqli_num_rows($result) == 0) {
    header("Location: manage_products.php");
    exit();
}

$product = mysqli_fetch_assoc($result);
$cat_query = "SELECT * FROM categories ORDER BY category_name";
$cat_result = mysqli_query($conn, $cat_query);

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $product_name = mysqli_real_escape_string($conn, $_POST['product_name']);
    $product_description = mysqli_real_escape_string($conn, $_POST['product_description']);
    $category_id = mysqli_real_escape_string($conn, $_POST['category_id']);
    $price = floatval($_POST['price']);
    $min_negotiation_price = floatval($_POST['min_negotiation_price']);
    $stock_quantity = (int)$_POST['stock_quantity'];
    $product_status = mysqli_real_escape_string($conn, $_POST['product_status']);

    $image_update = "";
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
                $image_update = ", product_image='$file_name'";
            }
        }
    }

    $update_query = "UPDATE products SET 
                     product_name='$product_name', 
                     product_description='$product_description', 
                     category_id='$category_id', 
                     price='$price', 
                     min_negotiation_price='$min_negotiation_price', 
                     stock_quantity='$stock_quantity', 
                     product_status='$product_status'
                     $image_update 
                     WHERE id='$product_id' AND vendor_id='$vendor_id'";

    if (mysqli_query($conn, $update_query)) {
        header("Location: manage_products.php?success=Product updated successfully");
        exit();
    } else {
        $error = "Failed to update product.";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Product - AI Price Negotiator</title>
    <link rel="stylesheet" href="../assests/css/style.css">
</head>
<body>

<div class="navbar">
    <span class="brand">AI Price Negotiator - Vendor Panel</span>
    <ul class="nav-links">
        <li><a href="dashboard.php">Dashboard</a></li>
        <li><a href="manage_products.php">Products</a></li>
        <li><a href="orders.php">Orders</a></li>
        <li><a href="logout.php">Logout</a></li>
    </ul>
</div>

<div class="form-container" style="max-width:600px;">
    <h2>Edit Product</h2>

    <?php if (isset($error)) { ?>
        <div class="alert alert-error"><?php echo $error; ?></div>
    <?php } ?>

    <form method="POST" enctype="multipart/form-data">
        <div class="form-group">
            <label>Product Name</label>
            <input type="text" name="product_name" value="<?php echo htmlspecialchars($product['product_name']); ?>" required>
        </div>

        <div class="form-group">
            <label>Description</label>
            <textarea name="product_description" rows="4" required><?php echo htmlspecialchars($product['product_description']); ?></textarea>
        </div>

        <div class="form-group">
            <label>Category</label>
            <select name="category_id" required>
                <?php while ($cat = mysqli_fetch_assoc($cat_result)) { ?>
                    <option value="<?php echo $cat['id']; ?>" <?php if ($cat['id'] == $product['category_id']) echo 'selected'; ?>>
                        <?php echo htmlspecialchars($cat['category_name']); ?>
                    </option>
                <?php } ?>
            </select>
        </div>

        <div class="form-group">
            <label>Price (Rs.)</label>
            <input type="number" name="price" step="0.01" min="1" value="<?php echo $product['price']; ?>" required>
        </div>

        <div class="form-group">
            <label>Minimum Negotiation Price (Rs.)</label>
            <input type="number" name="min_negotiation_price" step="0.01" min="1" value="<?php echo $product['min_negotiation_price']; ?>" required>
        </div>

        <div class="form-group">
            <label>Stock Quantity</label>
            <input type="number" name="stock_quantity" min="0" value="<?php echo $product['stock_quantity']; ?>" required>
        </div>

        <div class="form-group">
            <label>Status</label>
            <select name="product_status" required>
                <option value="Active" <?php if ($product['product_status'] == 'Active') echo 'selected'; ?>>Active</option>
                <option value="Inactive" <?php if ($product['product_status'] == 'Inactive') echo 'selected'; ?>>Inactive</option>
            </select>
        </div>

        <div class="form-group">
            <label>Product Image (leave empty to keep current)</label>
            <input type="file" name="product_image" accept="image/*">
            <?php if ($product['product_image']) { ?>
                <small>Current: <?php echo $product['product_image']; ?></small>
            <?php } ?>
        </div>

        <button type="submit" class="btn btn-primary btn-block mt-10">Update Product</button>
    </form>

    <p class="text-center mt-20"><a href="manage_products.php">Back to Products</a></p>
</div>

</body>
</html>
