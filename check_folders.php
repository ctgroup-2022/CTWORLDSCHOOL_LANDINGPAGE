<?php
// filepath: c:\xampp\htdocs\CTWORLDSCHOOL_LANDINGPAGE-main\CTWORLDSCHOOL_LANDINGPAGE-main\check_folders.php

// Create all necessary directories with proper permissions
$directories = [
    'uploads',
    'uploads/payment_screenshots',
    'assets/images/qr'
];

foreach ($directories as $dir) {
    if(!is_dir($dir)) {
        if(mkdir($dir, 0755, true)) {
            echo "Successfully created directory: " . $dir . "<br>";
        } else {
            echo "<strong style='color:red'>Failed to create directory: " . $dir . "</strong><br>";
            echo "Please manually create this folder with write permissions.<br>";
        }
    } else {
        echo "Directory exists: " . $dir . " (Writable: " . (is_writable($dir) ? 'Yes' : 'No') . ")<br>";
    }
}
?>