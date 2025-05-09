<style>
    body {
      background: linear-gradient(to right, #f8f9fa, #e9ecef);
      margin-bottom: 200px;
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
  <div class="container d-flex justify-content-center align-items-center vh-0">
    <div class="card p-4 shadow-lg w-100" style="max-width: 450px;">
      <h2 class="text-center fw-bolder" style="color: #198754;">Apply Now</h2>
      <p class="text-center fw-bolder" style="color:rgb(235, 216, 49);">CT Shooting Championship</p>

      <form action="config/controller.php" method="POST">
        <div class="mb-3">
          <label class="form-label" style="color:#198754;">Full Name</label>
          <div class="input-group">
            <span class="input-group-text" style="background-color:#ffc107;"><i class="fa fa-user"></i></span>
            <input type="text" name="name" class="form-control" placeholder="Enter your name" required>
          </div>
        </div>

        <div class="mb-3">
          <label class="form-label" style="color:#198754;">Phone Number</label>
          <div class="input-group">
            <span class="input-group-text" style="background-color:#ffc107;"><i class="fa fa-phone"></i></span>
            <input type="tel" name="phone" class="form-control" placeholder="Enter your phone number" required>
          </div>
        </div>

        <div class="mb-3">
          <label class="form-label" style="color:#198754;">Age</label>
          <div class="input-group">
            <span class="input-group-text" style="background-color:#ffc107;"><i class="fa fa-user"></i></span>
            <input type="tel" name="age" class="form-control" placeholder="Enter your Age" required>
          </div>
        </div>

        <div class="mb-3">
          <label class="form-label" style="color:#198754;">Gender</label>
          <div class="d-flex flex-wrap">
            <div class="form-check me-3">
              <input class="form-check-input" type="radio" name="gender" id="genderMale" value="1">
              <label class="form-check-label" for="genderMale">Male</label>
            </div>
            <div class="form-check me-3">
              <input class="form-check-input" type="radio" name="gender" id="genderFemale" value="2" checked>
              <label class="form-check-label" for="genderFemale">Female</label>
            </div>
          </div>
        </div>

        <div class="mb-3">
          <label class="form-label" style="color:#198754;">Participants</label>
          <div class="d-flex flex-wrap">
            <div class="form-check me-3">
              <input class="form-check-input" type="radio" name="participants" id="participantSchool" value="1">
              <label class="form-check-label" for="participantSchool">School</label>
            </div>
            <div class="form-check me-3">
              <input class="form-check-input" type="radio" name="participants" id="participantClub" value="2" checked>
              <label class="form-check-label" for="participantClub">Club</label>
            </div>
            <div class="form-check me-3">
              <input class="form-check-input" type="radio" name="participants" id="participantIndividual" value="3">
              <label class="form-check-label" for="participantIndividual">Individual</label>
            </div>
          </div>
        </div>
        
        <button type="submit" name="submit" class="btn w-100" style="background-color:#ffc107;color:#198754;">
          <i class="fa fa-paper-plane" style="color:#198754;"></i> Submit
        </button>
      </form>

    </div>
  </div>
</body>

<script>
  $(document).ready(function(){
    $('#enquiryForm').on('submit', function(e){
      e.preventDefault(); // stop normal form submit
      $.ajax({
        type: "POST",
        url: "insert_form.php",
        data: $(this).serialize(),
        success: function(response){
          if(response.trim() == "success"){
            alert("Data added successfully!");
            $('#enquiryForm')[0].reset(); // Reset the form
          } else {
            alert("Error: " + response);
          }
        }
      });
    });
  });

  // Add this JavaScript to form.php
  document.querySelector('form').addEventListener('submit', function(e) {
    const phone = document.querySelector('input[name="phone"]').value;
    const age = document.querySelector('input[name="age"]').value;
    
    // Validate phone number
    if (!/^\d{10}$/.test(phone)) {
        alert('Please enter a valid 10-digit phone number.');
        e.preventDefault();
        return;
    }
    
    // Validate age
    if (isNaN(age) || age < 6 || age > 99) {
        alert('Please enter a valid age between 6 and 99.');
        e.preventDefault();
        return;
    }
  });
</script>