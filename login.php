<?php
// Clear any existing session problems
if (session_status() == PHP_SESSION_ACTIVE) {
    session_write_close();
}

// Start fresh session
session_start();

// Include database connection
require_once 'config/controller.php';

// Debug login process
$debug_mode = true; // Set to false in production
function debug_log($message) {
    global $debug_mode;
    if ($debug_mode) {
        error_log('Auth Debug: ' . $message);
    }
}

// Add near the top of login.php
debug_log('Request method: ' . $_SERVER['REQUEST_METHOD']);
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    debug_log('POST data received: ' . json_encode($_POST));
}

// Test database connection
try {
    debug_log('Testing DB connection...');
    $test_query = "SELECT 1";
    $result = $conn->query($test_query);
    if ($result) {
        debug_log('Database connection successful');
    } else {
        debug_log('Database query failed');
    }
    
    // Check if admins table exists and has records
    $admin_check = "SELECT COUNT(*) as count FROM admins";
    $result = $conn->query($admin_check);
    if ($result) {
        $row = $result->fetch_assoc();
        debug_log('Admins in database: ' . $row['count']);
    } else {
        debug_log('Failed to query admins table: ' . $conn->error);
    }
} catch (Exception $e) {
    debug_log('Exception: ' . $e->getMessage());
}

// Initialize variables
$username = '';
$password = '';
$error_message = '';
$login_attempt = false;

// Process login attempt
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $login_attempt = true;
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';
    
    // Validate credentials
    if (empty($username) || empty($password)) {
        $error_message = 'Please enter both username and password.';
    } else {
        // Prevent SQL injection
        $username = $conn->real_escape_string($username);
        
        // Query the database for the admin user
        $query = "SELECT * FROM admins WHERE username = ?";
        $stmt = $conn->prepare($query);
        $stmt->bind_param('s', $username);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result && $result->num_rows === 1) {
            $admin = $result->fetch_assoc();
            
            // Debug password checking
            debug_log('Attempting password verification');
            debug_log('Stored hash: ' . substr($admin['password'], 0, 10) . '...');
            
            // Use proper password verification
            $password_ok = password_verify($password, $admin['password']);
            
            if ($password_ok) {
                debug_log('Password accepted');
                // Set session variables
                $_SESSION['admin_logged_in'] = true;
                $_SESSION['admin_id'] = $admin['id'];
                $_SESSION['admin_name'] = $admin['name'];
                $_SESSION['admin_username'] = $admin['username'];
                
                // Log successful login
                $ip = $_SERVER['REMOTE_ADDR'];
                $user_agent = $_SERVER['HTTP_USER_AGENT'];
                $log_query = "INSERT INTO admin_activity_logs (admin_id, admin_name, action, details, ip_address, user_agent) 
                              VALUES (?, ?, 'login', 'Admin login successful', ?, ?)";
                
                $log_stmt = $conn->prepare($log_query);
                $log_stmt->bind_param("isss", $admin['id'], $admin['name'], $ip, $user_agent);
                $log_stmt->execute();
                
                // Redirect to admin dashboard
                header("Location: admin.php");
                exit;
            } else {
                $error_message = 'Invalid username or password.';
                
                // Log failed login attempt
                $ip = $_SERVER['REMOTE_ADDR'];
                $user_agent = $_SERVER['HTTP_USER_AGENT'];
                $log_query = "INSERT INTO admin_activity_logs (admin_id, admin_name, action, details, ip_address, user_agent) 
                              VALUES (NULL, ?, 'login_failed', 'Failed login attempt with username: {$username}', ?, ?)";
                
                $log_stmt = $conn->prepare($log_query);
                $log_stmt->bind_param("sss", $username, $ip, $user_agent);
                $log_stmt->execute();
            }
        } else {
            $error_message = 'Invalid username or password.';
            
            // Log failed login attempt
            $ip = $_SERVER['REMOTE_ADDR'];
            $user_agent = $_SERVER['HTTP_USER_AGENT'];
            $log_query = "INSERT INTO admin_activity_logs (admin_id, admin_name, action, details, ip_address, user_agent) 
                          VALUES (NULL, ?, 'login_failed', 'Failed login attempt with username: {$username}', ?, ?)";
            
            $log_stmt = $conn->prepare($log_query);
            $log_stmt->bind_param("sss", $username, $ip, $user_agent);
            $log_stmt->execute();
        }
    }
}

// Redirect if already logged in
if (isset($_SESSION['admin_logged_in']) && $_SESSION['admin_logged_in'] === true) {
    header("Location: admin.php");
    exit;
}
?>
<!DOCTYPE html>
<html lang="en" data-bs-theme="light">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Login - CT Shooting Championship</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        :root {
            --primary-color: #0d6efd;
            --primary-hover: #0b5ed7;
            --success-color: #198754;
            --warning-color: #ffc107;
            --danger-color: #dc3545;
            --light-bg: #f8f9fa;
            --dark-bg: #212529;
            --card-bg-light: #ffffff;
            --card-bg-dark: #2c3034;
            --border-radius: 10px;
            --box-shadow: 0 4px 12px rgba(0,0,0,0.08);
        }

        [data-bs-theme="dark"] {
            --primary-color: #3a86ff;
            --card-shadow: 0 4px 12px rgba(0,0,0,0.2);
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Poppins', sans-serif;
            background: linear-gradient(135deg, #f8f9fa, #e9ecef);
            height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            overflow-x: hidden;
        }

        [data-bs-theme="dark"] body {
            background: linear-gradient(135deg, #212529, #343a40);
        }

        .login-container {
            width: 100%;
            max-width: 420px;
            padding: 1rem;
        }

        .login-card {
            background: var(--card-bg-light);
            border-radius: var(--border-radius);
            box-shadow: var(--box-shadow);
            overflow: hidden;
            border: none;
            transition: transform 0.3s ease;
        }

        [data-bs-theme="dark"] .login-card {
            background-color: var(--card-bg-dark);
            box-shadow: var(--card-shadow);
        }

        .login-card:hover {
            transform: translateY(-5px);
        }

        .card-header {
            background-color: var(--primary-color);
            color: white;
            padding: 2rem 1.5rem;
            text-align: center;
            border-bottom: none;
        }

        .card-logo {
            font-size: 2.5rem;
            margin-bottom: 1rem;
            display: inline-block;
            width: 80px;
            height: 80px;
            line-height: 80px;
            border-radius: 50%;
            background-color: rgba(255, 255, 255, 0.2);
        }

        .card-title {
            font-weight: 600;
            margin: 0;
            font-size: 1.5rem;
        }

        .card-subtitle {
            color: rgba(255, 255, 255, 0.8);
            font-size: 0.9rem;
            margin-top: 0.5rem;
        }

        .card-body {
            padding: 2rem 1.5rem;
        }

        .form-group {
            margin-bottom: 1.5rem;
        }

        .form-label {
            font-weight: 500;
            font-size: 0.9rem;
            margin-bottom: 0.5rem;
            color: #555;
        }

        [data-bs-theme="dark"] .form-label {
            color: #ccc;
        }

        .form-control {
            border: 2px solid #eee;
            border-radius: calc(var(--border-radius) - 4px);
            padding: 0.75rem 1rem;
            transition: all 0.2s;
            font-size: 0.95rem;
        }

        [data-bs-theme="dark"] .form-control {
            background-color: #343a40;
            border-color: #495057;
            color: #dee2e6;
        }

        .form-control:focus {
            border-color: var(--primary-color);
            box-shadow: 0 0 0 0.2rem rgba(13, 110, 253, 0.15);
        }

        [data-bs-theme="dark"] .form-control:focus {
            box-shadow: 0 0 0 0.2rem rgba(58, 134, 255, 0.15);
        }

        .input-group-text {
            background-color: #f8f9fa;
            border: 2px solid #eee;
            border-right: none;
            color: #555;
        }

        [data-bs-theme="dark"] .input-group-text {
            background-color: #495057;
            border-color: #495057;
            color: #ccc;
        }

        .btn-primary {
            background-color: var(--primary-color);
            border: none;
            border-radius: calc(var(--border-radius) - 4px);
            padding: 0.75rem 1.5rem;
            font-weight: 500;
            transition: all 0.2s;
            width: 100%;
        }

        .btn-primary:hover {
            background-color: var(--primary-hover);
            transform: translateY(-2px);
        }

        .error-message {
            color: var(--danger-color);
            font-size: 0.85rem;
            margin-top: 0.5rem;
            display: block;
        }

        .login-footer {
            text-align: center;
            margin-top: 1rem;
            font-size: 0.85rem;
            color: #666;
        }

        [data-bs-theme="dark"] .login-footer {
            color: #aaa;
        }

        .theme-toggle {
            position: absolute;
            top: 1.5rem;
            right: 1.5rem;
            color: #555;
            background: var(--card-bg-light);
            border: none;
            width: 40px;
            height: 40px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.2rem;
            cursor: pointer;
            transition: all 0.2s;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
        }

        [data-bs-theme="dark"] .theme-toggle {
            background: var(--card-bg-dark);
            color: #ccc;
            box-shadow: 0 2px 8px rgba(0,0,0,0.3);
        }

        .theme-toggle:hover {
            transform: rotate(30deg);
        }

        /* Animation for invalid form state */
        @keyframes shake {
            0%, 100% { transform: translateX(0); }
            10%, 30%, 50%, 70%, 90% { transform: translateX(-5px); }
            20%, 40%, 60%, 80% { transform: translateX(5px); }
        }

        .shake {
            animation: shake 0.6s;
        }

        /* Responsive adjustments */
        @media (max-width: 576px) {
            .login-container {
                padding: 0 1rem;
            }
            
            .card-logo {
                width: 70px;
                height: 70px;
                line-height: 70px;
                font-size: 2rem;
            }
            
            .card-title {
                font-size: 1.3rem;
            }
        }
    </style>
</head>
<body>
    <button class="theme-toggle" id="theme-toggle">
        <i class="fas fa-moon"></i>
    </button>
    
    <div class="login-container">
        <div class="login-card card">
            <div class="card-header">
                <div class="card-logo">
                    <i class="fas fa-trophy"></i>
                </div>
                <h3 class="card-title">Admin Dashboard</h3>
                <p class="card-subtitle">CT Shooting Championship</p>
            </div>
            
            <div class="card-body">
                <?php if (!empty($error_message)): ?>
                <div class="alert alert-danger" role="alert">
                    <i class="fas fa-exclamation-circle me-2"></i>
                    <?php echo $error_message; ?>
                </div>
                <?php endif; ?>
                
                <form method="POST" action="login.php" id="loginForm" class="<?php echo ($login_attempt && !empty($error_message)) ? 'shake' : ''; ?>">
                    <div class="form-group">
                        <label for="username" class="form-label">Username</label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="fas fa-user"></i></span>
                            <input type="text" class="form-control" id="username" name="username" placeholder="Enter your username" value="<?php echo htmlspecialchars($username); ?>" required>
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label for="password" class="form-label">Password</label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="fas fa-lock"></i></span>
                            <input type="password" class="form-control" id="password" name="password" placeholder="Enter your password" required>
                            <button class="btn btn-outline-secondary" type="button" id="togglePassword">
                                <i class="fas fa-eye"></i>
                            </button>
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-sign-in-alt me-2"></i> Log In
                        </button>
                    </div>
                </form>
            </div>
        </div>
        
        <div class="login-footer">
            &copy; <?php echo date('Y'); ?> CT Shooting Championship. All rights reserved.
        </div>
    </div>
    
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Theme toggle functionality
            const themeToggle = document.getElementById('theme-toggle');
            const themeIcon = themeToggle.querySelector('i');
            const htmlElement = document.documentElement;
            
            // Check local storage for saved theme preference
            const savedTheme = localStorage.getItem('theme');
            if (savedTheme === 'dark') {
                htmlElement.setAttribute('data-bs-theme', 'dark');
                themeIcon.classList.remove('fa-moon');
                themeIcon.classList.add('fa-sun');
            }
            
            themeToggle.addEventListener('click', function() {
                if (htmlElement.getAttribute('data-bs-theme') === 'dark') {
                    htmlElement.setAttribute('data-bs-theme', 'light');
                    themeIcon.classList.remove('fa-sun');
                    themeIcon.classList.add('fa-moon');
                    localStorage.setItem('theme', 'light');
                } else {
                    htmlElement.setAttribute('data-bs-theme', 'dark');
                    themeIcon.classList.remove('fa-moon');
                    themeIcon.classList.add('fa-sun');
                    localStorage.setItem('theme', 'dark');
                }
            });
            
            // Toggle password visibility
            const togglePassword = document.getElementById('togglePassword');
            const passwordField = document.getElementById('password');
            
            togglePassword.addEventListener('click', function() {
                const type = passwordField.getAttribute('type') === 'password' ? 'text' : 'password';
                passwordField.setAttribute('type', type);
                
                // Toggle eye icon
                const eyeIcon = this.querySelector('i');
                eyeIcon.classList.toggle('fa-eye');
                eyeIcon.classList.toggle('fa-eye-slash');
            });
        });
    </script>
</body>
</html>