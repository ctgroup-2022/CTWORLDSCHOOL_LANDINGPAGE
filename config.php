<?php
// config.php

// Database configuration
$host = "localhost";            // or your server IP
$username = "your_db_username"; // replace with your actual DB username
$password = "your_db_password"; // replace with your actual DB password
$database = "your_db_name";     // replace with your actual DB name

// Create connection
$con = mysqli_connect($host, $username, $password, $database);

// Check connection
if (!$con) {
    die("Connection failed: " . mysqli_connect_error());
}
?>
