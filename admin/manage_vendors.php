<?php
require_once("../config/config.php");

if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'admin') {
    header("Location: login.php");
    exit();
}

if (isset($_GET['action']) && isset($_GET['id'])) {
    $vendor_id = mysqli_real_escape_string($conn, $_GET['id']);
    $action = $_GET['action'];

    if ($action == 'approve') {
        mysqli_query($conn, "UPDATE users SET approval_status='Approved' WHERE id='$vendor_id' AND role='vendor'");
        header("Location: manage_vendors.php?success=Vendor approved successfully");
        exit();
    } elseif ($action == 'reject') {
        mysqli_query($conn, "UPDATE users SET approval_status='Rejected' WHERE id='$vendor_id' AND role='vendor'");
        header("Location: manage_vendors.php?success=Vendor rejected");
        exit();
    }
}

$filter = isset($_GET['filter']) ? $_GET['filter'] : 'all';
$where = "WHERE role='vendor'";
if ($filter == 'pending') $where .= " AND approval_status='Pending'";
elseif ($filter == 'approved') $where .= " AND approval_status='Approved'";
elseif ($filter == 'rejected') $where .= " AND approval_status='Rejected'";

$vendors_query = "SELECT * FROM users $where ORDER BY created_at DESC";
$vendors_result = mysqli_query($conn, $vendors_query);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Vendors - Admin</title>
    <link rel="stylesheet" href="../assests/css/style.css">
</head>
<body>

<div class="page-wrapper">
    <div class="sidebar">
        <div class="sidebar-header">Admin Panel</div>
        <ul class="sidebar-menu">
            <li><a href="dashboard.php">Dashboard</a></li>
            <li><a href="manage_vendors.php" class="active">Manage Vendors</a></li>
            <li><a href="manage_users.php">Manage Users</a></li>
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
            <h1>Manage Vendors</h1>
        </div>

        <?php if (isset($_GET['success'])) { ?>
            <div class="alert alert-success"><?php echo htmlspecialchars($_GET['success']); ?></div>
        <?php } ?>

        <!-- Filter -->
        <div class="search-bar mb-20">
            <a href="manage_vendors.php?filter=all" class="btn <?php echo ($filter == 'all') ? 'btn-primary' : 'btn-secondary'; ?> btn-sm">All</a>
            <a href="manage_vendors.php?filter=pending" class="btn <?php echo ($filter == 'pending') ? 'btn-warning' : 'btn-secondary'; ?> btn-sm">Pending</a>
            <a href="manage_vendors.php?filter=approved" class="btn <?php echo ($filter == 'approved') ? 'btn-success' : 'btn-secondary'; ?> btn-sm">Approved</a>
            <a href="manage_vendors.php?filter=rejected" class="btn <?php echo ($filter == 'rejected') ? 'btn-danger' : 'btn-secondary'; ?> btn-sm">Rejected</a>
        </div>

        <?php if (mysqli_num_rows($vendors_result) > 0) { ?>
            <table class="data-table">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Username</th>
                        <th>Email</th>
                        <th>Phone</th>
                        <th>Business Name</th>
                        <th>GST Number</th>
                        <th>Status</th>
                        <th>Registered</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($vendor = mysqli_fetch_assoc($vendors_result)) { ?>
                        <tr>
                            <td><?php echo $vendor['id']; ?></td>
                            <td><?php echo htmlspecialchars($vendor['username']); ?></td>
                            <td><?php echo htmlspecialchars($vendor['email']); ?></td>
                            <td><?php echo $vendor['phone']; ?></td>
                            <td><?php echo htmlspecialchars($vendor['business_name']); ?></td>
                            <td><?php echo htmlspecialchars($vendor['gst_number']); ?></td>
                            <td>
                                <?php 
                                $s_class = 'info';
                                if ($vendor['approval_status'] == 'Approved') $s_class = 'success';
                                elseif ($vendor['approval_status'] == 'Rejected') $s_class = 'danger';
                                elseif ($vendor['approval_status'] == 'Pending') $s_class = 'warning';
                                ?>
                                <span class="badge badge-<?php echo $s_class; ?>"><?php echo $vendor['approval_status']; ?></span>
                            </td>
                            <td><?php echo date('d M Y', strtotime($vendor['created_at'])); ?></td>
                            <td>
                                <?php if ($vendor['approval_status'] == 'Pending') { ?>
                                    <a href="manage_vendors.php?action=approve&id=<?php echo $vendor['id']; ?>" class="btn btn-success btn-sm">Approve</a>
                                    <a href="manage_vendors.php?action=reject&id=<?php echo $vendor['id']; ?>" class="btn btn-danger btn-sm">Reject</a>
                                <?php } elseif ($vendor['approval_status'] == 'Approved') { ?>
                                    <a href="manage_vendors.php?action=reject&id=<?php echo $vendor['id']; ?>" class="btn btn-danger btn-sm">Revoke</a>
                                <?php } elseif ($vendor['approval_status'] == 'Rejected') { ?>
                                    <a href="manage_vendors.php?action=approve&id=<?php echo $vendor['id']; ?>" class="btn btn-success btn-sm">Approve</a>
                                <?php } ?>
                            </td>
                        </tr>
                    <?php } ?>
                </tbody>
            </table>
        <?php } else { ?>
            <div class="card">
                <p class="text-center">No vendors found for this filter.</p>
            </div>
        <?php } ?>
    </div>
</div>

</body>
</html>
