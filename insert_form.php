<?php
if (isset($_POST['name'])) {
$db_pass = "FwlDeBo3smizxNx";
    $conn = mysqli_connect("localhost", "root", "FwlDeBo3smizxNx ", "WorldSchool_Landing_Page-2025");

    if (!$conn) {
        echo "Database connection failed: " . mysqli_connect_error();
        exit;
    }

    $name = $_POST['name'];
    $phone = $_POST['phone'];
    $age = $_POST['age'];
    $gender = $_POST['gender'];
    $participants = $_POST['participants'];

    $query = "INSERT INTO registrations(name, phone_number, age, gender, participants, status, created_at) 
              VALUES ('$name', '$phone', '$age', '$gender', '$participants', 'Pending', NOW())";

    if (mysqli_query($conn, $query)) {
        echo "success";
    } else {
        echo "Error: " . mysqli_error($conn);
    }

    mysqli_close($conn);
}
?>
