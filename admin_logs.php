<?php
// Start session
session_start();

// Database connection
require_once 'config/controller.php';

// Check if admin is logged in
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header("Location: admin.php");
    exit;
}

// Get activity logs
$query = "SELECT * FROM admin_activity_logs ORDER BY created_at DESC LIMIT 100";
$result = $conn->query($query);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Activity Logs - CT Shooting Championship</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        body {
            background: #f8f9fa;
            font-family: 'Poppins', sans-serif;
        }
        .navbar {
            background-color: #198754 !important;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        .navbar-brand {
            font-weight: bold;
            color: #fff !important;
        }
        .table-container {
            background: white;
            border-radius: 10px;
            padding: 20px;
            box-shadow: 0 4px 10px rgba(0,0,0,0.05);
        }
        .activity-login {
            color: #198754;
        }
        .activity-logout {
            color: #ffc107;
        }
        .activity-update {
            color: #0d6efd;
        }
    </style>
</head>
<body>
    <!-- Navbar -->
    <nav class="navbar navbar-expand-lg navbar-dark bg-primary mb-4">
        <div class="container">
            <a class="navbar-brand" href="admin.php">
                <i class="fas fa-trophy me-2"></i> CT Shooting Championship Admin
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item">
                        <a class="nav-link text-white" href="admin.php">
                            <i class="fas fa-tachometer-alt me-1"></i> Dashboard
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link text-white active" href="admin_logs.php">
                            <i class="fas fa-history me-1"></i> Activity Logs
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link text-white" href="admin.php?action=logout">
                            <i class="fas fa-sign-out-alt me-1"></i> Logout
                        </a>
                    </li>
                </ul>
            </div>
        </div>
    </nav>
    
    <div class="container mb-5">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h3>Admin Activity Logs</h3>
            <a href="admin.php" class="btn btn-outline-success">
                <i class="fas fa-arrow-left me-1"></i> Back to Dashboard
            </a>
        </div>
        
        <div class="table-container">
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead class="table-light">
                        <tr>
                            <th>Date & Time</th>
                            <th>Admin</th>
                            <th>Action</th>
                            <th>Details</th>
                            <th>IP Address</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php 
                        if ($result && $result->num_rows > 0) {
                            while($row = $result->fetch_assoc()) {
                                // Determine icon and color based on action
                                $icon = 'fa-info-circle';
                                $class = '';
                                
                                if ($row['action'] == 'login') {
                                    $icon = 'fa-sign-in-alt';
                                    $class = 'activity-login';
                                } else if ($row['action'] == 'logout') {
                                    $icon = 'fa-sign-out-alt';
                                    $class = 'activity-logout';
                                } else if (strpos($row['action'], 'update') !== false) {
                                    $icon = 'fa-edit';
                                    $class = 'activity-update';
                                }
                                
                                // Format date
                                $date = date('M d, Y h:i A', strtotime($row['created_at']));
                                
                                echo "<tr>
                                    <td>{$date}</td>
                                    <td>{$row['admin_name']}</td>
                                    <td><i class='fas {$icon} {$class} me-2'></i>{$row['action']}</td>
                                    <td>{$row['details']}</td>
                                    <td>{$row['ip_address']}</td>
                                </tr>";
                            }
                        } else {
                            echo "<tr><td colspan='5' class='text-center py-4'>No activity logs found</td></tr>";
                        }
                        ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    
    <footer class="bg-light py-3 text-center mt-auto">
        <div class="container">
            <p class="mb-0">CT Shooting Championship Admin Dashboard &copy; <?php echo date('Y'); ?></p>
        </div>
    </footer>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>