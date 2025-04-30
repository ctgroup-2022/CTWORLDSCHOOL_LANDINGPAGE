<?php
// Start the session
session_start();

// Get the name from session if available
$participant_name = isset($_SESSION['participant_name']) ? $_SESSION['participant_name'] : '';

// Add this at the top of your qr.php file
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    include 'config/controller.php'; // Reuse the database connection
    
    // Sanitize input
    $name = $conn->real_escape_string(trim($_POST['name']));
    $selected_plan = $conn->real_escape_string(trim($_POST['plan']));
    
    // Handle file upload for payment screenshot
    $screenshot_path = '';
    $upload_success = false;
    
    // Improved file upload handler
    if(isset($_FILES['payment_screenshot']) && $_FILES['payment_screenshot']['error'] == 0) {
        // Create directory if it doesn't exist
        $upload_dir = 'uploads/payment_screenshots/';
        if(!is_dir($upload_dir)) {
            if(!mkdir($upload_dir, 0755, true)) {
                echo "<script>alert('Failed to create upload directory. Please contact admin.');</script>";
                $upload_success = false;
            }
        }
        
        if(is_dir($upload_dir) && is_writable($upload_dir)) {
            // Get file info and generate unique filename
            $file_extension = pathinfo($_FILES['payment_screenshot']['name'], PATHINFO_EXTENSION);
            $new_filename = 'payment_' . time() . '_' . rand(1000, 9999) . '.' . $file_extension;
            $target_file = $upload_dir . $new_filename;
            
            // Only allow image uploads
            $allowed_types = ['jpg', 'jpeg', 'png', 'gif'];
            if(in_array(strtolower($file_extension), $allowed_types)) {
                if(move_uploaded_file($_FILES['payment_screenshot']['tmp_name'], $target_file)) {
                    $screenshot_path = $target_file;
                    $upload_success = true;
                    // Log successful upload
                    error_log("File successfully uploaded to: " . $target_file);
                } else {
                    error_log("Failed to move uploaded file to: " . $target_file);
                }
            } else {
                echo "<script>alert('Only JPG, PNG and GIF files are allowed.');</script>";
            }
        } else {
            echo "<script>alert('Upload directory is not writable: " . $upload_dir . "');</script>";
        }
    }
    
    if($upload_success) {
        // Update the registration with payment info
        $sql = "UPDATE registrations SET 
                payment_status = 'Pending Verification', 
                transaction_id = 'Screenshot Uploaded',
                payment_plan = '$selected_plan',
                payment_proof = '$screenshot_path'
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
    } else {
        echo "<script>alert('Error uploading screenshot. Please try again.');</script>";
    }
    
    $conn->close();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Payment Options - CT Shooting Championship</title>
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
        .pricing-card {
            border: 2px solid #dee2e6;
            border-radius: 10px;
            padding: 20px;
            margin-bottom: 20px;
            transition: all 0.3s ease;
            position: relative;
            overflow: hidden;
        }
        .pricing-card.selected {
            border-color: #198754;
            box-shadow: 0 5px 15px rgba(25, 135, 84, 0.3);
        }
        .pricing-card:hover {
            transform: translateY(-5px);
        }
        .price-tag {
            font-size: 2rem;
            font-weight: bold;
            color: #198754;
        }
        .price-currency {
            font-size: 1rem;
            position: relative;
            top: -10px;
        }
        .benefit-list {
            margin-top: 20px;
            padding-left: 0;
        }
        .benefit-list li {
            list-style: none;
            padding: 5px 0;
        }
        .benefit-list i {
            color: #198754;
            margin-right: 10px;
        }
        .plan-badge {
            position: absolute;
            top: 0;
            right: 0;
            background-color: #ffc107;
            color: #212529;
            padding: 5px 10px;
            border-radius: 0 0 0 10px;
            font-weight: bold;
            font-size: 0.8rem;
        }
        .section-title {
            position: relative;
            padding-bottom: 10px;
            margin-bottom: 20px;
            text-align: center;
        }
        .section-title:after {
            content: '';
            position: absolute;
            bottom: 0;
            left: 50%;
            transform: translateX(-50%);
            width: 50px;
            height: 3px;
            background-color: #ffc107;
        }
        .file-upload {
            position: relative;
            overflow: hidden;
            margin-top: 20px;
        }
        .file-upload input[type=file] {
            font-size: 100px;
            position: absolute;
            left: 0;
            top: 0;
            opacity: 0;
            cursor: pointer;
        }
        .upload-btn {
            display: inline-block;
            border: 2px dashed #198754;
            border-radius: 10px;
            padding: 30px 20px;
            text-align: center;
            width: 100%;
            transition: all 0.3s ease;
            background-color: rgba(25, 135, 84, 0.05);
        }
        .upload-btn:hover {
            background-color: rgba(25, 135, 84, 0.1);
        }
        #preview-container {
            display: none;
            margin-top: 15px;
        }
        #image-preview {
            max-width: 100%;
            max-height: 200px;
            border-radius: 8px;
            border: 3px solid #198754;
        }

        /* Add this to your existing CSS in qr.php */
        @media (max-width: 767px) {
            .pricing-card {
                margin-bottom: 20px;
            }
            
            .card {
                margin: 0 10px;
            }
            
            .qr-image {
                max-width: 180px !important;
            }
            
            .btn-payment {
                padding: 10px;
                font-size: 0.9rem;
            }
            
            .upload-btn {
                padding: 20px 10px;
            }
            
            .card-body {
                padding: 1.5rem 1rem;
            }
        }

        /* Ensure preview image doesn't overflow */
        #image-preview {
            max-width: 100% !important;
            height: auto !important;
        }

        /* Fixes for very small devices */
        @media (max-width: 375px) {
            .price-tag {
                font-size: 1.5rem;
            }
            
            .benefit-list li {
                font-size: 0.9rem;
            }
            
            .qr-image {
                max-width: 150px !important;
            }
        }

        /* Add these general fixes to both admin.php and qr.php */

        /* Fix container padding on mobile */
        @media (max-width: 767px) {
            .container {
                padding-left: 15px;
                padding-right: 15px;
            }
            
            h3, h4 {
                font-size: 1.5rem;
            }
            
            .mb-4 {
                margin-bottom: 1rem !important;
            }
        }

        /* Improve form control sizing on mobile */
        @media (max-width: 576px) {
            .form-control, .input-group-text, .btn {
                font-size: 0.9rem;
            }
            
            label.form-label {
                font-size: 0.9rem;
            }
        }
    </style>
</head>
<body>
    <div class="container py-5">
        <div class="card mx-auto" style="max-width: 800px;">
            <div class="card-header text-center text-white py-3">
                <h3><i class="fas fa-trophy me-2"></i> CT Shooting Championship</h3>
                <p class="mb-0 text-warning">Choose Your Registration Plan</p>
            </div>
            <div class="card-body p-4">
                <h4 class="section-title">Select Payment Plan</h4>
                
                <div class="row">
                    <div class="col-md-6">
                        <div class="pricing-card" id="standard-plan" onclick="selectPlan('standard')">
                            <div class="plan-badge">FRESHER</div>
                            <h5>Fresher</h5>
                            <div class="price-tag"><span class="price-currency">₹</span>50</div>
                            <p class="text-muted">For beginners</p>
                            <ul class="benefit-list">
                                <li><i class="fas fa-check-circle"></i> Championship Entry</li>
                                <li><i class="fas fa-check-circle"></i> Provision of Guns</li>
                                <li><i class="fas fa-check-circle"></i> 5 Shots</li>
                                <li><i class="fas fa-check-circle"></i> Gift for Bull eye</li>
                            </ul>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="pricing-card" id="premium-plan" onclick="selectPlan('premium')">
                            <div class="plan-badge" style="background-color: #198754; color: white;">NR SHOOTERS</div>
                            <h5>NR Shooters</h5>
                            <div class="price-tag"><span class="price-currency">₹</span>500</div>
                            <p class="text-muted">For experienced shooters</p>
                            <ul class="benefit-list">
                                <li><i class="fas fa-check-circle"></i> Championship entry</li>
                                <li><i class="fas fa-check-circle"></i> 40 shots match</li>
                                <li><i class="fas fa-check-circle"></i> Medals and certificates for all</li>
                                <li><i class="fas fa-check-circle"></i> Attractive prizes for the winners</li>
                            </ul>
                        </div>
                    </div>
                </div>
                
                <h4 class="section-title mt-4">Make Your Payment</h4>
                
                <div class="text-center mb-4">
                </div>
                
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
                    After payment, upload your payment screenshot and submit the form to complete your registration
                </div>
                
                <form method="POST" action="" enctype="multipart/form-data">
                    <input type="hidden" name="plan" id="selected-plan" value="standard">
                    
                    <div class="mb-3">
                        <label for="name" class="form-label">Your Name</label>
                        <div class="input-group">
                            <span class="input-group-text" style="background-color:#ffc107;">
                                <i class="fa fa-user" style="color:#198754;"></i>
                            </span>
                            <input type="text" class="form-control" id="name" name="name" 
                                   value="<?php echo htmlspecialchars($participant_name); ?>" required>
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Payment Screenshot</label>
                        <div class="file-upload">
                            <div class="upload-btn" id="upload-area">
                                <i class="fas fa-cloud-upload-alt fa-2x mb-2" style="color:#198754;"></i>
                                <p class="mb-0">Click or drag and drop to upload your payment screenshot</p>
                                <small class="text-muted">(Supported formats: JPG, PNG, GIF)</small>
                            </div>
                            <input type="file" name="payment_screenshot" id="payment-screenshot" accept="image/*" required>
                        </div>
                        <div id="preview-container" class="text-center">
                            <p class="mb-2">Preview:</p>
                            <img id="image-preview" src="#" alt="Preview">
                        </div>
                    </div>
                    
                    <button type="submit" class="btn btn-payment w-100 mt-3">
                        <i class="fa fa-check-circle me-2"></i> Confirm Payment
                    </button>
                </form>
            </div>
        </div>
    </div>
    
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script>
        // Handle plan selection
        function selectPlan(plan) {
            // Update hidden input
            document.getElementById('selected-plan').value = plan;
            
            // Update UI
            if(plan === 'standard') {
                document.getElementById('standard-plan').classList.add('selected');
                document.getElementById('premium-plan').classList.remove('selected');
            } else {
                document.getElementById('premium-plan').classList.add('selected');
                document.getElementById('standard-plan').classList.remove('selected');
            }
        }
        
        // Initial plan selection
        selectPlan('standard');
        
        // Handle file upload preview
        document.getElementById('payment-screenshot').addEventListener('change', function(e) {
            const file = e.target.files[0];
            if(file) {
                const reader = new FileReader();
                
                reader.onload = function(event) {
                    document.getElementById('image-preview').src = event.target.result;
                    document.getElementById('preview-container').style.display = 'block';
                    document.getElementById('upload-area').innerHTML = '<i class="fas fa-check-circle fa-2x mb-2" style="color:#198754;"></i><p class="mb-0">File selected</p>';
                }
                
                reader.readAsDataURL(file);
            }
        });
    </script>
</body>
</html>