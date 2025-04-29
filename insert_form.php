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

    // Sanitize and store the name in the session
    $_SESSION['participant_name'] = htmlspecialchars($_POST['name']);

    $query = "INSERT INTO registrations(name, phone_number, age, gender, participants, status, created_at) 
              VALUES ('$name', '$phone', '$age', '$gender', '$participants', 'Pending', NOW())";

    if (mysqli_query($conn, $query)) {
        echo "success";
        // After successful database insertion, redirect to QR page
        header("Location: qr.php");
        exit;
    } else {
        echo "Error: " . mysqli_error($conn);
    }

    mysqli_close($conn);
}
?>
