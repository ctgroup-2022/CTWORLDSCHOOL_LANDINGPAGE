<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>QR Payment</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <style>
        body {
            background-color: #f8f9fa;
            font-family: 'Poppins', sans-serif;
        }
        .card {
            border: none;
            border-radius: 15px;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
        }
        .card-header {
            background-color: #198754;
            border-top-left-radius: 15px;
            border-top-right-radius: 15px;
        }
        .card-header h3 {
            font-weight: bold;
            letter-spacing: 1px;
        }
        .btn-primary {
            background-color: #ffc107;
            border: none;
            color: #198754;
            font-weight: bold;
            transition: all 0.3s ease;
        }
        .btn-primary:hover {
            background-color: #198754;
            color: #ffc107;
        }
        .form-control {
            border-radius: 10px;
        }
        .qr-image {
            border: 5px solid #ffc107;
            border-radius: 15px;
            padding: 10px;
            background-color: #fff;
        }
    </style>
</head>
<body>
    <div class="container mt-5">
        <div class="card mx-auto" style="max-width: 500px;">
            <div class="card-header text-center text-white">
                <h3>Scan & Pay</h3>
            </div>
            <div class="card-body">
                <div class="text-center mb-4">
                    <!-- QR Code Image -->
                    <img src="assets/images/qr/qr.jpeg" alt="QR Code" class="img-fluid qr-image" style="max-width: 200px;">
                    <p class="mt-3 text-muted">Scan the QR code above to make your payment</p>
                </div>
                <form method="POST" action="">
                    <div class="mb-3">
                        <label for="name" class="form-label" style="color: #198754; font-weight: bold;">Name</label>
                        <input type="text" class="form-control" id="name" name="name" placeholder="Enter your name" required>
                    </div>
                    <div class="mb-3">
                        <label for="transaction_id" class="form-label" style="color: #198754; font-weight: bold;">Transaction ID</label>
                        <input type="text" class="form-control" id="transaction_id" name="transaction_id" placeholder="Enter transaction ID" required>
                    </div>
                    <button type="submit" class="btn btn-primary w-100">Submit</button>
                </form>
            </div>
        </div>
    </div>
</body>
</html>