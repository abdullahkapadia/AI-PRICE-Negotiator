<?php
require_once("../config/config.php");

if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'admin') {
    header("Location: login.php");
    exit();
}

require_once("../negotiation engine/negotiate_process.php");
$engine = new NegotiationEngine($conn);

$train_result = null;
$model_status = $engine->getModelStatus();

if (isset($_POST['train_model'])) {
    $train_result = $engine->trainModel();
    $model_status = $engine->getModelStatus();
}

if (isset($_POST['export_data'])) {
    include("../negotiation engine/export_training_data.php");
    $export_done = true;
}

$total_sessions = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) AS c FROM negotiations_sessions"))['c'];
$accepted = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) AS c FROM negotiations_sessions WHERE status='Accepted'"))['c'];
$rejected_expired = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) AS c FROM negotiations_sessions WHERE status IN ('Rejected','Expired')"))['c'];
$active = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) AS c FROM negotiations_sessions WHERE status='Active'"))['c'];
$avg_discount = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COALESCE(AVG((original_price - final_price) / original_price * 100), 0) AS a FROM negotiations_sessions WHERE status='Accepted' AND final_price > 0"))['a'];
$total_logs = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) AS c FROM negotiation_logs"))['c'];

$pending_vendors = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) AS c FROM users WHERE role='vendor' AND approval_status='Pending'"))['c'];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ML Model Training - Admin</title>
    <link rel="stylesheet" href="../assests/css/style.css">
</head>
<body>

<div class="page-wrapper">
    <div class="sidebar">
        <div class="sidebar-header">Admin Panel</div>
        <ul class="sidebar-menu">
            <li><a href="dashboard.php">Dashboard</a></li>
            <li><a href="manage_vendors.php">Manage Vendors <?php if ($pending_vendors > 0) echo "($pending_vendors)"; ?></a></li>
            <li><a href="manage_users.php">Manage Users</a></li>
            <li><a href="manage_products.php">Manage Products</a></li>
            <li><a href="manage_categories.php">Categories</a></li>
            <li><a href="negotiation_logs.php">Negotiation Logs</a></li>
            <li><a href="orders.php">Orders</a></li>
            <li><a href="ml_training.php" class="active">ML Training</a></li>
            <li><a href="logout.php">Logout</a></li>
        </ul>
    </div>

    <div class="main-content">
        <div class="page-header">
            <h1>AI Negotiation - ML Model Training</h1>
        </div>

        <?php if ($train_result) { ?>
            <div class="alert alert-<?php echo ($train_result['success']) ? 'success' : 'error'; ?>">
                <?php echo htmlspecialchars($train_result['message'] ?? $train_result['error'] ?? 'Training completed.'); ?>
            </div>
        <?php } ?>

        <?php if (isset($export_done)) { ?>
            <div class="alert alert-success">Training data exported to CSV successfully.</div>
        <?php } ?>

        <!-- System Status -->
        <div class="stats-grid">
            <div class="stat-card" style="border-top-color:<?php echo $model_status['python_available'] ? '#059669' : '#dc2626'; ?>;">
                <div class="stat-number"><?php echo $model_status['python_available'] ? 'Available' : 'Not Found'; ?></div>
                <div class="stat-label">Python Status</div>
            </div>
            <div class="stat-card">
                <div class="stat-number"><?php echo $total_sessions; ?></div>
                <div class="stat-label">Total Sessions</div>
            </div>
            <div class="stat-card" style="border-top-color:#059669;">
                <div class="stat-number"><?php echo $accepted; ?></div>
                <div class="stat-label">Accepted Deals</div>
            </div>
            <div class="stat-card" style="border-top-color:#7c3aed;">
                <div class="stat-number"><?php echo number_format($avg_discount, 1); ?>%</div>
                <div class="stat-label">Avg Discount Given</div>
            </div>
        </div>

        <div style="display:flex; gap:20px; flex-wrap:wrap;">
            <!-- Model Status Card -->
            <div class="card" style="flex:1; min-width:300px;">
                <div class="card-header">Model Status</div>
                <table class="data-table">
                    <tbody>
                        <tr>
                            <td><strong>Python</strong></td>
                            <td>
                                <?php if ($model_status['python_available']) { ?>
                                    <span class="badge badge-success">Detected</span>
                                    <small style="color:#64748b; margin-left:8px;"><?php echo htmlspecialchars($model_status['python_path']); ?></small>
                                <?php } else { ?>
                                    <span class="badge badge-danger">Not Found</span>
                                    <small style="color:#94a3b8; margin-left:8px;">Install Python and add to PATH</small>
                                <?php } ?>
                            </td>
                        </tr>
                        <tr>
                            <td><strong>Trained Model</strong></td>
                            <td>
                                <?php if ($model_status['model_exists'] && $model_status['model_info']) { ?>
                                    <span class="badge badge-success">Ready</span>
                                    <br><small style="color:#64748b;">
                                        Type: <?php echo ucfirst(str_replace('_', ' ', $model_status['model_info']['type'])); ?> |
                                        Accuracy: <?php echo $model_status['model_info']['accuracy']; ?>% |
                                        Trained on: <?php echo $model_status['model_info']['samples']; ?> samples
                                    </small>
                                <?php } else { ?>
                                    <span class="badge badge-warning">Not Trained</span>
                                    <small style="color:#94a3b8; margin-left:8px;">Click "Train Model" to start</small>
                                <?php } ?>
                            </td>
                        </tr>
                        <tr>
                            <td><strong>Training Data</strong></td>
                            <td>
                                <?php echo $total_logs; ?> negotiation records available
                                <br><small style="color:#64748b;">From <?php echo $total_sessions; ?> sessions (<?php echo $accepted; ?> accepted, <?php echo $rejected_expired; ?> rejected/expired, <?php echo $active; ?> active)</small>
                            </td>
                        </tr>
                        <tr>
                            <td><strong>Fallback Mode</strong></td>
                            <td>
                                <span class="badge badge-info">Active</span>
                                <small style="color:#64748b; margin-left:8px;">PHP rule-based engine used when Python/model unavailable</small>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <!-- Actions Card -->
            <div class="card" style="flex:0 0 320px;">
                <div class="card-header">Actions</div>
                
                <form method="POST" style="margin-bottom:15px;">
                    <p style="color:#475569; margin-bottom:10px;">Step 1: Export negotiation data from the database to a CSV file for model training.</p>
                    <button type="submit" name="export_data" class="btn btn-secondary" style="width:100%;">Export Training Data (CSV)</button>
                </form>

                <form method="POST" style="margin-bottom:15px;">
                    <p style="color:#475569; margin-bottom:10px;">Step 2: Train the Random Forest model on the exported data.</p>
                    <button type="submit" name="train_model" class="btn btn-primary" style="width:100%;" <?php echo (!$model_status['python_available']) ? 'disabled title="Python not available"' : ''; ?>>
                        Train ML Model
                    </button>
                </form>

                <?php if (!$model_status['python_available']) { ?>
                    <div class="alert alert-warning" style="margin-top:15px;">
                        Python is not detected on this system. Please install Python 3.x and ensure it is added to the system PATH. The ML model requires Python to train and make predictions. Without Python, the system uses the built-in PHP fallback engine.
                    </div>
                <?php } ?>
            </div>
        </div>

        <!-- How It Works -->
        <div class="card mt-20">
            <div class="card-header">How the ML Negotiation Engine Works</div>
            <div style="display:flex; gap:30px; flex-wrap:wrap;">
                <div style="flex:1; min-width:250px;">
                    <h3 style="font-size:16px; color:#1e293b; margin-bottom:10px;">Features Analyzed (13 inputs)</h3>
                    <table class="data-table">
                        <thead>
                            <tr><th>Feature</th><th>Description</th></tr>
                        </thead>
                        <tbody>
                            <tr><td>Offer Percentage</td><td>Customer's offer as % of original price</td></tr>
                            <tr><td>Customer Orders</td><td>Total completed orders by customer</td></tr>
                            <tr><td>Customer Spending</td><td>Total amount spent by customer</td></tr>
                            <tr><td>Product Stock</td><td>Current stock quantity</td></tr>
                            <tr><td>Days Listed</td><td>How long the product has been listed</td></tr>
                            <tr><td>Total Sales</td><td>Total units sold of this product</td></tr>
                            <tr><td>Active Negotiations</td><td>Ongoing negotiations for this product</td></tr>
                            <tr><td>Success Rate</td><td>Customer's past negotiation success rate</td></tr>
                            <tr><td>Avg Discount</td><td>Average discount customer has received</td></tr>
                            <tr><td>Round Number</td><td>Current negotiation round (max 5)</td></tr>
                            <tr><td>Category</td><td>Product category identifier</td></tr>
                            <tr><td>Original Price</td><td>Listed product price</td></tr>
                            <tr><td>Min Price %</td><td>Vendor's minimum acceptable price %</td></tr>
                        </tbody>
                    </table>
                </div>
                <div style="flex:1; min-width:250px;">
                    <h3 style="font-size:16px; color:#1e293b; margin-bottom:10px;">ML Pipeline</h3>
                    <div style="line-height:2;">
                        <p><strong>1. Data Export:</strong> PHP extracts negotiation records with computed features into a CSV file.</p>
                        <p><strong>2. Model Training:</strong> Python trains a Random Forest (15 decision trees) on the CSV data using Gini impurity splitting.</p>
                        <p><strong>3. Model Storage:</strong> The trained model is serialized to JSON for fast loading.</p>
                        <p><strong>4. Real-time Prediction:</strong> During negotiation, PHP calls Python with 13 features. Python loads the model, predicts Accept/Counter/Reject with confidence score, and returns JSON.</p>
                        <p><strong>5. Fallback:</strong> If Python is unavailable, PHP uses a built-in rule-based engine that considers loyalty, demand, and pricing factors.</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

</body>
</html>
