<?php


// Database connection
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "world_school";

$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Check if form is submitted
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Retrieve form data
    $name = $conn->real_escape_string($_POST['name']);
    $phone = $conn->real_escape_string($_POST['phone']);
    $age = $conn->real_escape_string($_POST['email']); // Assuming 'email' is used for age
    $gender = isset($_POST['radioDefault']) ? $conn->real_escape_string($_POST['radioDefault']) : '';
    $class = isset($_POST['radioDefault']) ? $conn->real_escape_string($_POST['radioDefault']) : '';

    // Insert data into the database
    $sql = "INSERT INTO students (name, phone, age, gender, class) VALUES ('$name', '$phone', '$age', '$gender', '$class')";

    if ($conn->query($sql) === TRUE) {
        echo "New record created successfully";
    } else {
        echo "Error: " . $sql . "<br>" . $conn->error;
    }
}

// Close connection
$conn->close();
?>