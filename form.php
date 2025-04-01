<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Enquire Now</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
    <style>
        body {
            background: linear-gradient(to right, #f8f9fa, #e9ecef);
            margin-bottom:200px;
        }
        .card {
            border-radius: 12px;
            background: linear-gradient(135deg, #ffffff, #f3f3f3);
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
            transition: transform 0.3s ease-in-out;
        }
        .card:hover {
            transform: scale(1.02);
        }
        .form-label {
            font-weight: 600;
        }
        .input-group-text {
            background: #007bff;
            color: white;
            border: none;
        }
        .form-control {
            border: 2px solid #dee2e6;
            transition: all 0.3s ease;
        }
        .form-control:focus {
            border-color: #007bff;
            box-shadow: 0 0 5px rgba(0, 123, 255, 0.5);
        }
        .btn-primary {
            background-color: #007bff;
            border: none;
            font-weight: 600;
            transition: background 0.3s ease;
        }
        .btn-primary:hover {
            background-color: #0056b3;
        }
    </style>
</head>
<body class="bg-light">
    <div class="container d-flex justify-content-center align-items-center vh-100">
        <div class="card p-4 shadow-lg w-100" style="max-width: 450px;">
            <h2 class="text-center fw-bolder" style="color: #198754;">Apply  Now</h2>
            <p class="text-center text-muted">Start your journey with us</p>
            <form action="process.php" method="POST">
                
                <!-- Full Name -->
                <div class="mb-3">
                    <label class="form-label"style="color:#198754;">Full Name</label>
                    <div class="input-group">
                        <span class="input-group-text" style="background-color:#ffc107;"><i class="fa fa-user" "></i></span>
                        <input type="text" name="name" class="form-control" placeholder="Enter your name" required>
                    </div>
                </div>

                <!-- Phone Number -->
                <div class="mb-3">
                    <label class="form-label"style="color:#198754;">Phone Number</label>
                    <div class="input-group">
                        <span class="input-group-text" style="background-color:#ffc107;"><i class="fa fa-phone"></i></span>
                        <input type="tel" name="phone" class="form-control" placeholder="Enter your phone number" required>
                    </div>
                </div>

                <!-- Email Address -->
                <div class="mb-3">
                    <label class="form-label"style="color:#198754;">Email Address</label>
                    <div class="input-group">
                        <span class="input-group-text" style="background-color:#ffc107;"><i class="fa fa-envelope"></i></span>
                        <input type="email" name="email" class="form-control" placeholder="Enter your email" required>
                    </div>
                </div>

                <!-- State Selection -->
                

                <!-- Class Selection -->
                <div class="mb-3">
                    <label class="form-label" style="color:#198754;">Class</label>
                    <div class="input-group">
                        <span class="input-group-text" style="background-color:#ffc107;"><i class="fa fa-book"></i></span>
                        <select class="form-select">
  <option selected>Class</option>
  <option value="1">one</option>
  <option value="2">Two</option>
  <option value="3">Three</option>
  <option value="1">One</option>
  <option value="2">Two</option>
  <option value="3">Three</option>
  <option value="1">One</option>
  <option value="2">Two</option>
  <option value="3">Three</option>
</select>
                    </div>
                </div>

                <!-- Submit Button -->
                <button type="submit" class="btn w-100" style="background-color:#ffc107;color:#198754;  ">
                    <i class="fa fa-paper-plane" style="color:#198754;"></i> Submit
                </button>
            </form>
        </div>
    </div>
</body>
</html>