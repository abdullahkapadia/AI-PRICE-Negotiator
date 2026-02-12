<?php
require_once("../config/config.php");

if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'admin') {
    header("Location: login.php");
    exit();
}

if (isset($_GET['action']) && isset($_GET['id'])) {
    $uid = mysqli_real_escape_string($conn, $_GET['id']);
    $action = $_GET['action'];

    if ($action == 'activate') {
        mysqli_query($conn, "UPDATE users SET active_status=1 WHERE id='$uid' AND role='customer'");
    } elseif ($action == 'deactivate') {
        mysqli_query($conn, "UPDATE users SET active_status=0 WHERE id='$uid' AND role='customer'");
    }
    header("Location: manage_users.php?success=User status updated");
    exit();
}

$users_query = "SELECT * FROM users WHERE role='customer' ORDER BY created_at DESC";
$users_result = mysqli_query($conn, $users_query);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Users - Admin</title>
    <link rel="stylesheet" href="../assests/css/style.css">
</head>
<body>

<div class="page-wrapper">
    <div class="sidebar">
        <div class="sidebar-header">Admin Panel</div>
        <ul class="sidebar-menu">
            <li><a href="dashboard.php">Dashboard</a></li>
            <li><a href="manage_vendors.php">Manage Vendors</a></li>
            <li><a href="manage_users.php" class="active">Manage Users</a></li>
            <li><a href="manage_products.php">Manage Products</a></li>
            <li><a href="manage_categories.php">Categories</a></li>
            <li><a href="negotiation_logs.php">Negotiation Logs</a></li>
            <li><a href="orders.php">Orders</a></li>
            <li><a href="ml_training.php">ML Training</a></li>
            <li><a href="logout.php">Logout</a></li>
        </ul>
    </div>

    <div class="main-content">
        <div class="page-header">
            <h1>Manage Customers</h1>
        </div>

        <?php if (isset($_GET['success'])) { ?>
            <div class="alert alert-success"><?php echo htmlspecialchars($_GET['success']); ?></div>
        <?php } ?>

        <?php if (mysqli_num_rows($users_result) > 0) { ?>
            <table class="data-table">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Username</th>
                        <th>Email</th>
                        <th>Phone</th>
                        <th>Status</th>
                        <th>Registered</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($user = mysqli_fetch_assoc($users_result)) { ?>
                        <tr>
                            <td><?php echo $user['id']; ?></td>
                            <td><?php echo htmlspecialchars($user['username']); ?></td>
                            <td><?php echo htmlspecialchars($user['email']); ?></td>
                            <td><?php echo $user['phone']; ?></td>
                            <td>
                                <?php if ($user['active_status']) { ?>
                                    <span class="badge badge-success">Active</span>
                                <?php } else { ?>
                                    <span class="badge badge-danger">Inactive</span>
                                <?php } ?>
                            </td>
                            <td><?php echo date('d M Y', strtotime($user['created_at'])); ?></td>
                            <td>
                                <?php if ($user['active_status']) { ?>
                                    <a href="manage_users.php?action=deactivate&id=<?php echo $user['id']; ?>" class="btn btn-danger btn-sm">Deactivate</a>
                                <?php } else { ?>
                                    <a href="manage_users.php?action=activate&id=<?php echo $user['id']; ?>" class="btn btn-success btn-sm">Activate</a>
                                <?php } ?>
                            </td>
                        </tr>
                    <?php } ?>
                </tbody>
            </table>
        <?php } else { ?>
            <div class="card">
                <p class="text-center">No customers registered yet.</p>
            </div>
        <?php } ?>
    </div>
</div>

</body>
</html>
