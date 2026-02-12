<?php
require_once("../config/config.php");

if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'admin') {
    header("Location: login.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['add_category'])) {
    $name = mysqli_real_escape_string($conn, $_POST['category_name']);
    $desc = mysqli_real_escape_string($conn, $_POST['category_description']);
    mysqli_query($conn, "INSERT INTO categories (category_name, category_description) VALUES ('$name', '$desc')");
    header("Location: manage_categories.php?success=Category added");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['edit_category'])) {
    $cat_id = mysqli_real_escape_string($conn, $_POST['category_id']);
    $name = mysqli_real_escape_string($conn, $_POST['category_name']);
    $desc = mysqli_real_escape_string($conn, $_POST['category_description']);
    mysqli_query($conn, "UPDATE categories SET category_name='$name', category_description='$desc' WHERE id='$cat_id'");
    header("Location: manage_categories.php?success=Category updated");
    exit();
}

if (isset($_GET['delete'])) {
    $cat_id = mysqli_real_escape_string($conn, $_GET['delete']);
    mysqli_query($conn, "DELETE FROM categories WHERE id='$cat_id'");
    header("Location: manage_categories.php?success=Category deleted");
    exit();
}

$cat_query = "SELECT * FROM categories ORDER BY category_name";
$cat_result = mysqli_query($conn, $cat_query);

$edit_cat = null;
if (isset($_GET['edit'])) {
    $edit_id = mysqli_real_escape_string($conn, $_GET['edit']);
    $edit_result = mysqli_query($conn, "SELECT * FROM categories WHERE id='$edit_id'");
    $edit_cat = mysqli_fetch_assoc($edit_result);
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Categories - Admin</title>
    <link rel="stylesheet" href="../assests/css/style.css">
</head>
<body>

<div class="page-wrapper">
    <div class="sidebar">
        <div class="sidebar-header">Admin Panel</div>
        <ul class="sidebar-menu">
            <li><a href="dashboard.php">Dashboard</a></li>
            <li><a href="manage_vendors.php">Manage Vendors</a></li>
            <li><a href="manage_users.php">Manage Users</a></li>
            <li><a href="manage_products.php">Manage Products</a></li>
            <li><a href="manage_categories.php" class="active">Categories</a></li>
            <li><a href="negotiation_logs.php">Negotiation Logs</a></li>
            <li><a href="orders.php">Orders</a></li>
            <li><a href="ml_training.php">ML Training</a></li>
            <li><a href="logout.php">Logout</a></li>
        </ul>
    </div>

    <div class="main-content">
        <div class="page-header">
            <h1>Manage Categories</h1>
        </div>

        <?php if (isset($_GET['success'])) { ?>
            <div class="alert alert-success"><?php echo htmlspecialchars($_GET['success']); ?></div>
        <?php } ?>

        <!-- Add / Edit Category Form -->
        <div class="card mb-20">
            <div class="card-header"><?php echo $edit_cat ? 'Edit Category' : 'Add New Category'; ?></div>
            <form method="POST" style="display:flex; gap:10px; align-items:flex-end; flex-wrap:wrap;">
                <?php if ($edit_cat) { ?>
                    <input type="hidden" name="category_id" value="<?php echo $edit_cat['id']; ?>">
                <?php } ?>
                <div class="form-group" style="flex:1; min-width:200px; margin-bottom:0;">
                    <label>Category Name</label>
                    <input type="text" name="category_name" value="<?php echo $edit_cat ? htmlspecialchars($edit_cat['category_name']) : ''; ?>" required>
                </div>
                <div class="form-group" style="flex:2; min-width:200px; margin-bottom:0;">
                    <label>Description</label>
                    <input type="text" name="category_description" value="<?php echo $edit_cat ? htmlspecialchars($edit_cat['category_description']) : ''; ?>">
                </div>
                <?php if ($edit_cat) { ?>
                    <button type="submit" name="edit_category" class="btn btn-primary">Update</button>
                    <a href="manage_categories.php" class="btn btn-secondary">Cancel</a>
                <?php } else { ?>
                    <button type="submit" name="add_category" class="btn btn-success">Add Category</button>
                <?php } ?>
            </form>
        </div>

        <!-- Categories Table -->
        <?php if (mysqli_num_rows($cat_result) > 0) { ?>
            <table class="data-table">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Category Name</th>
                        <th>Description</th>
                        <th>Created</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($cat = mysqli_fetch_assoc($cat_result)) { ?>
                        <tr>
                            <td><?php echo $cat['id']; ?></td>
                            <td><?php echo htmlspecialchars($cat['category_name']); ?></td>
                            <td><?php echo htmlspecialchars($cat['category_description']); ?></td>
                            <td><?php echo date('d M Y', strtotime($cat['created_at'])); ?></td>
                            <td>
                                <a href="manage_categories.php?edit=<?php echo $cat['id']; ?>" class="btn btn-primary btn-sm">Edit</a>
                                <a href="manage_categories.php?delete=<?php echo $cat['id']; ?>" class="btn btn-danger btn-sm" onclick="return confirm('Delete this category?')">Delete</a>
                            </td>
                        </tr>
                    <?php } ?>
                </tbody>
            </table>
        <?php } else { ?>
            <div class="card">
                <p class="text-center">No categories found.</p>
            </div>
        <?php } ?>
    </div>
</div>

</body>
</html>
