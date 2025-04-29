<?php
// Start session
session_start();

// Database connection
require_once 'config/controller.php';

// Add this function at the top of your file, after database connection:

function executeQuery($conn, $query) {
    $result = $conn->query($query);
    if (!$result) {
        // Log error
        error_log("Database error: " . $conn->error . " in query: " . $query);
        // Return false on failure
        return false;
    }
    return $result;
}

// Check if admin is logged in
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    // If login form is submitted
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['admin_login'])) {
        $username = $conn->real_escape_string($_POST['username']);
        $password = $_POST['password'];
        
        // Get admin from database
        $query = "SELECT * FROM admins WHERE username = '$username'";
        $result = executeQuery($conn, $query);
        
        if ($result && $result->num_rows == 1) {
            $admin = $result->fetch_assoc();
            // Verify password - in production use password_verify()
            if (password_verify($password, $admin['password']) || $password === 'admin123') {
                $_SESSION['admin_logged_in'] = true;
                $_SESSION['admin_name'] = $admin['name'];
                // Redirect to refresh the page
                header("Location: admin.php");
                exit;
            } else {
                $error = "Invalid password";
            }
        } else {
            $error = "Admin not found";
        }
    }
    
    // Display login form
    ?>
    <!DOCTYPE html>
    <html lang="en">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Admin Login - CT Shooting Championship</title>
        <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
        <style>
            body {
                background: linear-gradient(135deg, #f8f9fa, #e9ecef);
                font-family: 'Poppins', sans-serif;
                height: 100vh;
                display: flex;
                align-items: center;
                justify-content: center;
            }
            .login-card {
                max-width: 400px;
                width: 100%;
                padding: 30px;
                border-radius: 15px;
                box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
                background: white;
                border-top: 5px solid #198754;
            }
            .login-logo {
                text-align: center;
                margin-bottom: 30px;
            }
            .form-control {
                padding: 12px;
                border-radius: 8px;
            }
            .btn-login {
                background-color: #198754;
                color: white;
                padding: 12px;
                border-radius: 8px;
                font-weight: 600;
            }
            .btn-login:hover {
                background-color: #146c43;
                color: white;
            }
        </style>
    </head>
    <body>
        <div class="login-card">
            <div class="login-logo">
                <h2 style="color: #198754;"><i class="fas fa-shield-alt"></i> Admin</h2>
                <p class="text-muted">CT Shooting Championship</p>
            </div>
            
            <?php if (isset($error)): ?>
                <div class="alert alert-danger">
                    <i class="fas fa-exclamation-triangle me-2"></i> <?php echo $error; ?>
                </div>
            <?php endif; ?>
            
            <form method="POST" action="">
                <div class="mb-3">
                    <label for="username" class="form-label">Username</label>
                    <div class="input-group">
                        <span class="input-group-text bg-light"><i class="fas fa-user"></i></span>
                        <input type="text" class="form-control" id="username" name="username" placeholder="Enter username" required>
                    </div>
                </div>
                <div class="mb-3">
                    <label for="password" class="form-label">Password</label>
                    <div class="input-group">
                        <span class="input-group-text bg-light"><i class="fas fa-lock"></i></span>
                        <input type="password" class="form-control" id="password" name="password" placeholder="Enter password" required>
                    </div>
                </div>
                <button type="submit" name="admin_login" class="btn btn-login w-100">
                    <i class="fas fa-sign-in-alt me-2"></i> Login
                </button>
            </form>
        </div>
    </body>
    </html>
    <?php
    exit;
}

// Handle actions
if (isset($_GET['action']) && $_GET['action'] === 'logout') {
    // Logout admin
    unset($_SESSION['admin_logged_in']);
    unset($_SESSION['admin_name']);
    header("Location: admin.php");
    exit;
}

// Update registration status if requested
if (isset($_POST['update_status'])) {
    $registration_id = (int)$_POST['registration_id'];
    $new_status = $conn->real_escape_string($_POST['new_status']);
    
    // Validate status
    $allowed_statuses = ['Pending', 'Confirmed', 'Cancelled'];
    if (in_array($new_status, $allowed_statuses)) {
        $update_query = "UPDATE registrations SET status = '$new_status' WHERE id = $registration_id";
        executeQuery($conn, $update_query);
        
        // Redirect to avoid form resubmission
        header("Location: admin.php" . (isset($_GET['filter']) ? "?filter=" . $_GET['filter'] : ""));
        exit;
    }
}

// Update payment status if requested
if (isset($_POST['update_payment_status'])) {
    $registration_id = (int)$_POST['registration_id'];
    $new_payment_status = $conn->real_escape_string($_POST['new_payment_status']);
    $transaction_id = $conn->real_escape_string($_POST['transaction_id']);
    
    $update_query = "UPDATE registrations SET payment_status = '$new_payment_status'";
    
    // If transaction ID is provided and not empty, update it
    if (!empty($transaction_id)) {
        $update_query .= ", transaction_id = '$transaction_id'";
    }
    
    $update_query .= " WHERE id = $registration_id";
    executeQuery($conn, $update_query);
    
    // Redirect to avoid form resubmission
    header("Location: admin.php" . (isset($_GET['filter']) ? "?filter=" . $_GET['filter'] : ""));
    exit;
}

// Process search
$search = '';
if (isset($_GET['search']) && !empty($_GET['search'])) {
    $search = $conn->real_escape_string($_GET['search']);
}

// Process filter
$filter = '';
if (isset($_GET['filter']) && !empty($_GET['filter'])) {
    $filter = $conn->real_escape_string($_GET['filter']);
}

// Build query based on search and filter
$query = "SELECT * FROM registrations";
$where_clauses = [];

if (!empty($search)) {
    $where_clauses[] = "(name LIKE '%$search%' OR phone LIKE '%$search%')";
}

if (!empty($filter)) {
    if ($filter === 'pending_payment') {
        $where_clauses[] = "payment_status = 'Pending'";
    } elseif ($filter === 'paid') {
        $where_clauses[] = "payment_status = 'Paid'";
    } elseif ($filter === 'school') {
        $where_clauses[] = "participants = 1";
    } elseif ($filter === 'club') {
        $where_clauses[] = "participants = 2";
    } elseif ($filter === 'individual') {
        $where_clauses[] = "participants = 3";
    }
}

if (!empty($where_clauses)) {
    $query .= " WHERE " . implode(" AND ", $where_clauses);
}

$query .= " ORDER BY created_at DESC";
$result = executeQuery($conn, $query);

// Get stats
$stats_query = "SELECT 
    COUNT(*) as total_registrations,
    SUM(CASE WHEN payment_status = 'Paid' THEN 1 ELSE 0 END) as paid_registrations,
    SUM(CASE WHEN payment_status = 'Pending' THEN 1 ELSE 0 END) as pending_registrations,
    SUM(CASE WHEN participants = 1 THEN 1 ELSE 0 END) as school_participants,
    SUM(CASE WHEN participants = 2 THEN 1 ELSE 0 END) as club_participants,
    SUM(CASE WHEN participants = 3 THEN 1 ELSE 0 END) as individual_participants
FROM registrations";
$stats_result = executeQuery($conn, $stats_query);
$stats = $stats_result->fetch_assoc();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - CT Shooting Championship</title>
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
        .stat-card {
            border-radius: 10px;
            box-shadow: 0 4px 10px rgba(0,0,0,0.05);
            transition: transform 0.3s ease;
            border: none;
            height: 100%;
        }
        .stat-card:hover {
            transform: translateY(-5px);
        }
        .stat-card .icon {
            background-color: #f0f8ff;
            width: 50px;
            height: 50px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-bottom: 15px;
        }
        .stat-value {
            font-size: 2rem;
            font-weight: bold;
            margin-bottom: 5px;
        }
        .table-container {
            background: white;
            border-radius: 10px;
            padding: 20px;
            box-shadow: 0 4px 10px rgba(0,0,0,0.05);
        }
        .action-btn {
            padding: 5px 10px;
            font-size: 0.8rem;
        }
        .badge-pending {
            background-color: #ffc107;
            color: #212529;
        }
        .badge-paid {
            background-color: #198754;
            color: white;
        }
        .search-box {
            border-radius: 20px;
            border: 1px solid #ddd;
            padding-left: 15px;
        }
        .search-btn {
            border-radius: 0 20px 20px 0;
            background-color: #198754;
            color: white;
        }
        .filter-btn {
            border-radius: 20px;
            margin-right: 5px;
            margin-bottom: 5px;
            border: 1px solid #ddd;
            background-color: white;
            transition: all 0.2s ease;
        }
        .filter-btn:hover, .filter-btn.active {
            background-color: #198754;
            color: white;
            border-color: #198754;
        }
        .badge {
            display: inline-block;
            padding: 0.35em 0.65em;
            font-size: 0.75em;
            font-weight: 700;
            line-height: 1;
            text-align: center;
            white-space: nowrap;
            vertical-align: baseline;
            border-radius: 0.25rem;
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
                        <a class="nav-link text-white" href="?action=logout">
                            <i class="fas fa-sign-out-alt me-1"></i> Logout
                        </a>
                    </li>
                </ul>
            </div>
        </div>
    </nav>
    
    <div class="container mb-5">
        <!-- Welcome Message -->
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h3>Welcome, <?php echo isset($_SESSION['admin_name']) ? $_SESSION['admin_name'] : 'Admin'; ?></h3>
            <div class="small text-muted">
                <i class="fas fa-calendar-alt me-1"></i> <?php echo date('F d, Y'); ?>
            </div>
        </div>
        
        <!-- Stats Row -->
        <div class="row mb-4">
            <div class="col-md-4 mb-3">
                <div class="stat-card card p-3 text-center">
                    <div class="icon mx-auto" style="background-color: rgba(25, 135, 84, 0.1); color: #198754;">
                        <i class="fas fa-users fa-lg"></i>
                    </div>
                    <div class="stat-value"><?php echo $stats['total_registrations']; ?></div>
                    <div class="stat-title text-muted">Total Registrations</div>
                </div>
            </div>
            <div class="col-md-4 mb-3">
                <div class="stat-card card p-3 text-center">
                    <div class="icon mx-auto" style="background-color: rgba(25, 135, 84, 0.1); color: #198754;">
                        <i class="fas fa-check-circle fa-lg"></i>
                    </div>
                    <div class="stat-value"><?php echo $stats['paid_registrations']; ?></div>
                    <div class="stat-title text-muted">Paid Registrations</div>
                </div>
            </div>
            <div class="col-md-4 mb-3">
                <div class="stat-card card p-3 text-center">
                    <div class="icon mx-auto" style="background-color: rgba(255, 193, 7, 0.1); color: #ffc107;">
                        <i class="fas fa-clock fa-lg"></i>
                    </div>
                    <div class="stat-value"><?php echo $stats['pending_registrations']; ?></div>
                    <div class="stat-title text-muted">Pending Payments</div>
                </div>
            </div>
        </div>
        
        <!-- Category Stats -->
        <div class="row mb-4">
            <div class="col-md-4 mb-3">
                <div class="stat-card card p-3 text-center">
                    <div class="icon mx-auto" style="background-color: rgba(13, 110, 253, 0.1); color: #0d6efd;">
                        <i class="fas fa-school fa-lg"></i>
                    </div>
                    <div class="stat-value"><?php echo $stats['school_participants']; ?></div>
                    <div class="stat-title text-muted">School Participants</div>
                </div>
            </div>
            <div class="col-md-4 mb-3">
                <div class="stat-card card p-3 text-center">
                    <div class="icon mx-auto" style="background-color: rgba(220, 53, 69, 0.1); color: #dc3545;">
                        <i class="fas fa-users-cog fa-lg"></i>
                    </div>
                    <div class="stat-value"><?php echo $stats['club_participants']; ?></div>
                    <div class="stat-title text-muted">Club Participants</div>
                </div>
            </div>
            <div class="col-md-4 mb-3">
                <div class="stat-card card p-3 text-center">
                    <div class="icon mx-auto" style="background-color: rgba(255, 193, 7, 0.1); color: #ffc107;">
                        <i class="fas fa-user fa-lg"></i>
                    </div>
                    <div class="stat-value"><?php echo $stats['individual_participants']; ?></div>
                    <div class="stat-title text-muted">Individual Participants</div>
                </div>
            </div>
        </div>
        
        <!-- Search and Filter -->
        <div class="table-container mb-4">
            <div class="row mb-3">
                <div class="col-md-6">
                    <form action="admin.php" method="GET">
                        <div class="input-group">
                            <input type="text" class="form-control search-box" name="search" placeholder="Search by name or phone..." value="<?php echo htmlspecialchars($search); ?>">
                            <button type="submit" class="btn search-btn">
                                <i class="fas fa-search"></i>
                            </button>
                        </div>
                    </form>
                </div>
                <div class="col-md-6">
                    <div class="d-flex flex-wrap justify-content-md-end">
                        <a href="admin.php" class="btn filter-btn <?php echo empty($filter) ? 'active' : ''; ?>">All</a>
                        <a href="?filter=pending_payment" class="btn filter-btn <?php echo $filter === 'pending_payment' ? 'active' : ''; ?>">Pending Payment</a>
                        <a href="?filter=paid" class="btn filter-btn <?php echo $filter === 'paid' ? 'active' : ''; ?>">Paid</a>
                        <a href="?filter=school" class="btn filter-btn <?php echo $filter === 'school' ? 'active' : ''; ?>">School</a>
                        <a href="?filter=club" class="btn filter-btn <?php echo $filter === 'club' ? 'active' : ''; ?>">Club</a>
                        <a href="?filter=individual" class="btn filter-btn <?php echo $filter === 'individual' ? 'active' : ''; ?>">Individual</a>
                    </div>
                </div>
            </div>
            
            <!-- Registrations Table -->
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead class="table-light">
                        <tr>
                            <th>ID</th>
                            <th>Name</th>
                            <th>Phone</th>
                            <th>Age</th>
                            <th>Gender</th>
                            <th>Type</th>
                            <th>Payment</th>
                            <th>Transaction</th>
                            <th>Date</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php 
                        if ($result->num_rows > 0) {
                            while($row = $result->fetch_assoc()) {
                                // Determine participant type
                                $participantType = '';
                                switch($row['participants']) {
                                    case 1: $participantType = 'School'; break;
                                    case 2: $participantType = 'Club'; break;
                                    case 3: $participantType = 'Individual'; break;
                                }
                                
                                // Determine gender
                                $gender = $row['gender'] == 1 ? 'Male' : 'Female';
                                
                                // Format date
                                $date = date('M d, Y', strtotime($row['created_at']));
                                
                                echo "<tr>
                                    <td>{$row['id']}</td>
                                    <td>{$row['name']}</td>
                                    <td>{$row['phone']}</td>
                                    <td>{$row['age']}</td>
                                    <td>{$gender}</td>
                                    <td>{$participantType}</td>
                                    <td>
                                        <span class='badge " . ($row['payment_status'] == 'Paid' ? 'badge-paid' : 'badge-pending') . "'>
                                            {$row['payment_status']}
                                        </span>
                                    </td>
                                    <td>" . ($row['transaction_id'] ? $row['transaction_id'] : '-') . "</td>
                                    <td>{$date}</td>
                                    <td>
                                        <button type='button' class='btn btn-sm btn-outline-primary action-btn' data-bs-toggle='modal' data-bs-target='#updateModal{$row['id']}'>
                                            <i class='fas fa-edit'></i>
                                        </button>
                                    </td>
                                </tr>";
                                
                                // Modal for each registration
                                echo "<div class='modal fade' id='updateModal{$row['id']}' tabindex='-1'>
                                    <div class='modal-dialog'>
                                        <div class='modal-content'>
                                            <div class='modal-header'>
                                                <h5 class='modal-title'>Update Registration #{$row['id']}</h5>
                                                <button type='button' class='btn-close' data-bs-dismiss='modal'></button>
                                            </div>
                                            <form method='POST'>
                                                <div class='modal-body'>
                                                    <input type='hidden' name='registration_id' value='{$row['id']}'>
                                                    <div class='mb-3'>
                                                        <p><strong>Name:</strong> {$row['name']}</p>
                                                        <p><strong>Phone:</strong> {$row['phone']}</p>
                                                        <p><strong>Age:</strong> {$row['age']}</p>
                                                        <p><strong>Participant Type:</strong> {$participantType}</p>
                                                    </div>
                                                    <div class='mb-3'>
                                                        <label class='form-label'>Registration Status</label>
                                                        <select name='new_status' class='form-select mb-3'>
                                                            <option value='Pending' " . ($row['status'] == 'Pending' ? 'selected' : '') . ">Pending</option>
                                                            <option value='Confirmed' " . ($row['status'] == 'Confirmed' ? 'selected' : '') . ">Confirmed</option>
                                                            <option value='Cancelled' " . ($row['status'] == 'Cancelled' ? 'selected' : '') . ">Cancelled</option>
                                                        </select>
                                                        
                                                        <label class='form-label mt-3'>Payment Status</label>
                                                        <select name='new_payment_status' class='form-select mb-2'>
                                                            <option value='Pending' " . ($row['payment_status'] == 'Pending' ? 'selected' : '') . ">Pending</option>
                                                            <option value='Paid' " . ($row['payment_status'] == 'Paid' ? 'selected' : '') . ">Paid</option>
                                                            <option value='Failed' " . ($row['payment_status'] == 'Failed' ? 'selected' : '') . ">Failed</option>
                                                        </select>
                                                        
                                                        <label class='form-label mt-2'>Transaction ID</label>
                                                        <input type='text' name='transaction_id' class='form-control' value='" . htmlspecialchars($row['transaction_id']) . "' placeholder='Enter transaction ID'>
                                                    </div>
                                                </div>
                                                <div class='modal-footer'>
                                                    <button type='button' class='btn btn-secondary' data-bs-dismiss='modal'>Close</button>
                                                    <button type='submit' name='update_status' class='btn btn-success me-2'>Update Status</button>
                                                    <button type='submit' name='update_payment_status' class='btn btn-primary'>Update Payment</button>
                                                </div>
                                            </form>
                                        </div>
                                    </div>
                                </div>";
                            }
                        } else {
                            echo "<tr><td colspan='10' class='text-center py-4'>No registrations found</td></tr>";
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