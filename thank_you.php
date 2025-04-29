<?php
session_start();
// Check if user arrived here legitimately
if (!isset($_SESSION['payment_completed']) || $_SESSION['payment_completed'] !== true) {
    // Redirect to homepage if trying to access directly
    header("Location: ./");
    exit;
}

// Get participant name if available
$participant_name = isset($_SESSION['participant_name']) ? $_SESSION['participant_name'] : 'Participant';

// Clear the session variables after displaying the page
$_SESSION['payment_completed'] = false;
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registration Complete - CT Shooting Championship</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        body {
            background: linear-gradient(135deg, #f8f9fa, #e9ecef);
            font-family: 'Poppins', sans-serif;
            min-height: 100vh;
        }
        .success-card {
            border: none;
            border-radius: 15px;
            box-shadow: 0 10px 30px rgba(25, 135, 84, 0.2);
            overflow: hidden;
            transform: translateY(0);
            transition: all 0.5s ease;
            max-width: 600px;
            margin: 40px auto;
        }
        .success-card:hover {
            transform: translateY(-10px);
            box-shadow: 0 15px 35px rgba(25, 135, 84, 0.25);
        }
        .card-header {
            background-color: #198754;
            border-bottom: 5px solid #ffc107;
            padding: 30px 20px;
        }
        .checkmark-circle {
            width: 120px;
            height: 120px;
            background-color: #ffffff;
            border-radius: 50%;
            margin: 0 auto;
            padding: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
        }
        .checkmark {
            width: 80px;
            height: 80px;
            border-radius: 50%;
            display: block;
            stroke-width: 5;
            stroke: #198754;
            stroke-miterlimit: 10;
            animation: fill .4s ease-in-out .4s forwards, scale .3s ease-in-out .9s both;
        }
        .checkmark-circle .checkmark-check {
            transform-origin: 50% 50%;
            stroke-dasharray: 48;
            stroke-dashoffset: 48;
            animation: stroke 0.5s cubic-bezier(0.65, 0, 0.45, 1) 0.3s forwards;
        }
        @keyframes stroke {
            100% { stroke-dashoffset: 0; }
        }
        @keyframes scale {
            0%, 100% { transform: none; }
            50% { transform: scale3d(1.1, 1.1, 1); }
        }
        @keyframes fill {
            100% { box-shadow: inset 0px 0px 0px 50px transparent; }
        }
        .confetti {
            position: absolute;
            width: 10px;
            height: 10px;
            background-color: #ffc107;
            opacity: 0.7;
            animation: confetti 5s ease-in-out infinite;
        }
        @keyframes confetti {
            0% { transform: translate(0, 0) rotate(0deg); }
            100% { transform: translate(100px, 500px) rotate(360deg); opacity: 0; }
        }
        .btn-home {
            background-color: #ffc107;
            color: #198754;
            font-weight: 600;
            padding: 12px 24px;
            border-radius: 8px;
            transition: all 0.3s ease;
            border: none;
        }
        .btn-home:hover {
            background-color: #198754;
            color: #ffc107;
            transform: scale(1.05);
        }
        .next-steps {
            background-color: #f8f9fa;
            border-left: 4px solid #ffc107;
            border-radius: 0 8px 8px 0;
            padding: 15px;
            margin: 20px 0;
        }
    </style>
</head>
<body>
    <!-- Create some confetti for a celebratory effect -->
    <div id="confetti-container"></div>
    
    <div class="container py-5">
        <div class="success-card">
            <div class="card-header text-center">
                <div class="checkmark-circle">
                    <svg class="checkmark" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 52 52">
                        <circle class="checkmark-circle" cx="26" cy="26" r="25" fill="none"/>
                        <path class="checkmark-check" fill="none" d="M14.1 27.2l7.1 7.2 16.7-16.8"/>
                    </svg>
                </div>
            </div>
            
            <div class="card-body p-4 text-center">
                <h2 class="text-success fw-bold mb-3">Registration Complete!</h2>
                <p class="lead">Thank you, <?php echo htmlspecialchars($participant_name); ?>!</p>
                <p class="mb-4">Your registration for the CT Shooting Championship has been confirmed and your payment has been received.</p>
                
                <div class="next-steps text-start">
                    <h5><i class="fas fa-tasks me-2 text-success"></i> Next Steps:</h5>
                    <ul class="mb-0">
                        <li>Check your phone for a confirmation SMS</li>
                        <li>Arrive 30 minutes before your scheduled slot</li>
                        <li>Bring your ID and a copy of your registration</li>
                        <li>Follow all safety guidelines provided at the venue</li>
                    </ul>
                </div>
                
                <div class="alert alert-warning" role="alert">
                    <i class="fas fa-info-circle me-2"></i>
                    Event details and schedule will be sent to your registered phone number.
                </div>
                
                <a href="./" class="btn btn-home mt-3">
                    <i class="fas fa-home me-2"></i> Return to Homepage
                </a>
            </div>
            
            <div class="card-footer text-center py-3 text-muted">
                <small>If you have any questions, please contact us at <strong>info@ctshootingchampionship.com</strong></small>
            </div>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script>
        // Create confetti animation
        $(document).ready(function() {
            const confettiCount = 100;
            const container = document.getElementById('confetti-container');
            
            // Create the confetti pieces
            for (let i = 0; i < confettiCount; i++) {
                const confetti = document.createElement('div');
                confetti.className = 'confetti';
                
                // Random position
                confetti.style.left = Math.random() * 100 + 'vw';
                confetti.style.top = -10 + 'px';
                
                // Random color
                const colors = ['#ffc107', '#198754', '#0dcaf0', '#fd7e14', '#dc3545'];
                confetti.style.backgroundColor = colors[Math.floor(Math.random() * colors.length)];
                
                // Random size
                const size = Math.random() * 10 + 5;
                confetti.style.width = size + 'px';
                confetti.style.height = size + 'px';
                
                // Random rotation
                confetti.style.transform = `rotate(${Math.random() * 360}deg)`;
                
                // Random animation duration
                confetti.style.animationDuration = (Math.random() * 3 + 2) + 's';
                
                // Random animation delay
                confetti.style.animationDelay = (Math.random() * 2) + 's';
                
                container.appendChild(confetti);
            }
        });
    </script>
</body>
</html>