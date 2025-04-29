<?php
// Start the session
session_start();

// Add this at the top of your qr.php file
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['name']) && isset($_POST['transaction_id'])) {
    include 'config/controller.php'; // Reuse the database connection
    
    // Sanitize input
    $name = $conn->real_escape_string(trim($_POST['name']));
    $transaction_id = $conn->real_escape_string(trim($_POST['transaction_id']));
    
    // Update the registration with payment info
    $sql = "UPDATE registrations SET payment_status = 'Paid', transaction_id = '$transaction_id' 
            WHERE name = '$name' AND status = 'Pending' 
            ORDER BY created_at DESC LIMIT 1";
    
    if ($conn->query($sql) === TRUE) {
        // Set session variables for thank you page
        $_SESSION['payment_completed'] = true;
        $_SESSION['participant_name'] = $name;
        
        // Redirect to thank you page
        echo "<script>window.location.href='thank_you.php';</script>";
    } else {
        echo "<script>alert('Error recording payment: " . $conn->error . "');</script>";
    }
    
    $conn->close();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>QR Payment - CT Shooting Championship</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        body {
            background: linear-gradient(135deg, #f8f9fa, #e9ecef);
            font-family: 'Poppins', sans-serif;
        }
        .card {
            border: none;
            border-radius: 15px;
            box-shadow: 0 10px 20px rgba(0, 0, 0, 0.15);
            overflow: hidden;
            transition: transform 0.3s ease;
        }
        .card:hover {
            transform: translateY(-5px);
        }
        .card-header {
            background-color: #198754;
            border-bottom: 5px solid #ffc107;
        }
        .card-header h3 {
            font-weight: bold;
            letter-spacing: 1px;
        }
        .btn-payment {
            background-color: #ffc107;
            border: none;
            color: #198754;
            font-weight: bold;
            padding: 12px;
            border-radius: 8px;
            transition: all 0.3s ease;
        }
        .btn-payment:hover {
            background-color: #198754;
            color: #ffc107;
            transform: scale(1.03);
        }
        .form-control {
            border-radius: 8px;
            padding: 12px;
            border: 2px solid #dee2e6;
        }
        .form-control:focus {
            border-color: #198754;
            box-shadow: 0 0 10px rgba(25, 135, 84, 0.3);
        }
        .qr-container {
            position: relative;
            display: inline-block;
        }
        .qr-image {
            border: 5px solid #ffc107;
            border-radius: 15px;
            padding: 10px;
            background-color: #fff;
            transition: transform 0.3s ease;
        }
        .qr-overlay {
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            opacity: 0;
            transition: opacity 0.3s ease;
        }
        .qr-container:hover .qr-image {
            transform: scale(1.05);
        }
        .qr-container:hover .qr-overlay {
            opacity: 1;
        }
        .pulse {
            animation: pulse 2s infinite;
        }
        @keyframes pulse {
            0% { box-shadow: 0 0 0 0 rgba(255, 193, 7, 0.7); }
            70% { box-shadow: 0 0 0 15px rgba(255, 193, 7, 0); }
            100% { box-shadow: 0 0 0 0 rgba(255, 193, 7, 0); }
        }
    </style>
</head>
<body>
    <div class="container py-5">
        <div class="card mx-auto" style="max-width: 500px;">
            <div class="card-header text-center text-white py-3">
                <h3><i class="fas fa-qrcode me-2"></i> Scan & Pay</h3>
                <p class="mb-0 text-warning">Complete your registration payment</p>
            </div>
            <div class="card-body p-4">
                <div class="text-center mb-4">
                    <div class="qr-container">
                        <img src="assets/images/qr/qr.jpeg" alt="QR Code" class="img-fluid qr-image pulse" style="max-width: 220px;">
                        <div class="qr-overlay">
                            <i class="fas fa-scan fa-2x text-success"></i>
                        </div>
                    </div>
                    <p class="mt-3 text-muted"><i class="fas fa-info-circle me-1"></i> Scan the QR code to make your payment</p>
                </div>
                
                <div class="alert alert-warning">
                    <i class="fas fa-exclamation-triangle me-2"></i>
                    After payment, submit the transaction details below to complete your registration
                </div>
                
                <form method="POST" action="">
                    <div class="mb-3">
                        <label for="name" class="form-label">Your Name</label>
                        <div class="input-group">
                            <span class="input-group-text" style="background-color:#ffc107;">
                                <i class="fa fa-user" style="color:#198754;"></i>
                            </span>
                            <input type="text" class="form-control" id="name" name="name" placeholder="Enter name used in registration" required>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label for="transaction_id" class="form-label">Transaction ID/Reference</label>
                        <div class="input-group">
                            <span class="input-group-text" style="background-color:#ffc107;">
                                <i class="fa fa-receipt" style="color:#198754;"></i>
                            </span>
                            <input type="text" class="form-control" id="transaction_id" name="transaction_id" placeholder="Enter payment reference number" required>
                        </div>
                    </div>
                    <button type="submit" class="btn btn-payment w-100">
                        <i class="fa fa-check-circle me-2"></i> Confirm Payment
                    </button>
                </form>
            </div>
        </div>
    </div>
    
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
</body>
</html>