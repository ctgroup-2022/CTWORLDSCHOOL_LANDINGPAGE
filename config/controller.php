<?php
// Improved session and error handling
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Enable error reporting for development
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Define constants for frequently used paths
define('UPLOAD_DIR', __DIR__ . '/../uploads/');
define('SCREENSHOT_DIR', UPLOAD_DIR . 'payment_screenshots/');

// Create these directories if they don't exist
if (!file_exists(UPLOAD_DIR)) {
    mkdir(UPLOAD_DIR, 0755, true);
}
if (!file_exists(SCREENSHOT_DIR)) {
    mkdir(SCREENSHOT_DIR, 0755, true);
}

// $host = "localhost"; // Or try "127.0.0.1" if localhost doesn't work
$username = "ws_landingpage";
$password = "FwlDeBo3smizxNx"; // Empty password
$host = "localhost"; // Or try "127.0.0.1" if localhost doesn't work
$database = "WorldSchool_Landing_Page-2025";


// Create connection with error handling
try {
    $conn = new mysqli($host, $username, $password, $database);

    // Check connection
    if ($conn->connect_error) {
        throw new Exception("Connection failed: " . $conn->connect_error);
    }
} catch (Exception $e) {
    // Provide a user-friendly error message
    die("<div style='color:red; padding:20px; font-family:Arial; background:#f8d7da; border:1px solid #f5c6cb; border-radius:5px; margin:20px;'>
        <h3>Database Connection Error</h3>
        <p>Could not connect to the database. Please check:</p>
        <ol>
            <li>MySQL service is running in XAMPP</li>
            <li>Database name 'ct_shooting_championship' exists</li>
            <li>Database credentials are correct</li>
        </ol>
        <p>Technical details (for administrator): " . $e->getMessage() . "</p>
        </div>");
}

// Process form submission
if (isset($_POST['submit'])) {
    // Get form data
    $name = $conn->real_escape_string($_POST['name']);
    $phone = $conn->real_escape_string($_POST['phone']);
    $age = $conn->real_escape_string($_POST['age']);
    $gender = isset($_POST['gender']) ? (int)$_POST['gender'] : 1;
    $participants = isset($_POST['participants']) ? (int)$_POST['participants'] : 2;

    // Insert into database
    $sql = "INSERT INTO registrations (name, phone, age, gender, participants) 
            VALUES ('$name', '$phone', '$age', $gender, $participants)";

    if ($conn->query($sql) === TRUE) {
        // Store registration info in session
        $_SESSION['registration_id'] = $conn->insert_id;
        $_SESSION['participant_name'] = $name;

        // Redirect to payment page
        echo "<script>window.location.href='../qr.php';</script>";
    } else {
        echo "<script>alert('Error: " . $conn->error . "'); window.history.back();</script>";
    }
    exit;
}
