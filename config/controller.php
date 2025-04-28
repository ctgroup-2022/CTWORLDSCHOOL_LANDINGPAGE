<?php
// Database configuration
$host = "localhost";
$db_user = "ws_landingpage";
$db_pass = "FwlDeBo3smizxNx";
$db_name = "WorldSchool_Landing_Page-2025";



// Create connection
$conn = new mysqli($host, $db_user, $db_pass, $db_name);

// Check connection
if ($conn->connect_error) {
  die("Connection failed: " . $conn->connect_error);
}

// Check if form submitted
if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST['submit'])) {
  // Collect and sanitize data
  $name = $conn->real_escape_string(trim($_POST['name']));
  $phone = $conn->real_escape_string(trim($_POST['phone']));
  $age = $conn->real_escape_string(trim($_POST['age']));
  $gender = $conn->real_escape_string($_POST['gender']);
  $participants = $conn->real_escape_string($_POST['participants']);

  // SQL insert query
  $sql = "INSERT INTO ws_landingpage (name, phone_number, age, gender, participants, status, created_at)
          VALUES ('$name', '$phone', '$age', '$gender', '$participants', 'Pending', NOW())";

  if ($conn->query($sql) === TRUE) {
    echo "<script>alert('Data added successfully!'); window.location.href='../world_school/index.php';</script>";
  } else {
    echo "<script>alert('Error: " . $conn->error . "'); window.history.back();</script>";
  }

  $conn->close();
} else {
  // Redirect if accessed directly
  header("Location: ../world_school/index.php");
  exit;
}
?>
