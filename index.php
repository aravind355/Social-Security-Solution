<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Aravind Sarovar</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <style>
    body {
      font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
      background-color: #f8f9fa;
    }
    .navbar {
      background-color: #003366;
    }
    .navbar-brand {
      font-weight: bold;
      color: #fff !important;
    }
    .nav-link {
      color: #fff !important;
    }
    .hero-section {
      background: url('images/home_background.avif') 
      no-repeat center center/cover;
      height: 75vh;
      display: flex;
      align-items: center;
      justify-content: center;
      color: white;
      text-shadow: 2px 2px 5px rgba(0,0,0,0.5);
    }
    .hero-section h1 {
      font-size: 3rem;
      font-weight: 700;
    }
    .flat-card {
      border-radius: 15px;
      transition: all 0.3s ease;
    }
    .flat-card:hover {
      transform: translateY(-5px);
      box-shadow: 0 6px 20px rgba(0,0,0,0.15);
    }
  </style>
</head>
<body>

<nav class="navbar navbar-expand-lg navbar-dark">
  <div class="container">
    <a class="navbar-brand" href="#">Aravind Sarovar</a>
    <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
      <span class="navbar-toggler-icon"></span>
    </button>
    <div class="collapse navbar-collapse justify-content-end" id="navbarNav">
      <ul class="navbar-nav">
        <li class="nav-item"><a href="#" class="nav-link">Home</a></li>
        <li class="nav-item"><a href="#" class="nav-link">About</a></li>
        <li class="nav-item"><a href="#" class="nav-link">Contact</a></li>
        <li class="nav-item"><a href="login.php" class="btn btn-light btn-sm ms-3">Login</a></li>
      </ul>
    </div>
  </div>
</nav>

<section class="hero-section text-center">
  <div>
    <h1>Welcome to Aravind Sarovar</h1>
    <p class="lead">A premium gated community designed for modern living.</p>
  </div>
</section>

<div class="container my-5">
  <h2 class="text-center text-primary mb-4">Available Flats</h2>
  <div class="row g-4">
    <div class="col-md-4">
      <div class="card flat-card shadow-sm">
        <img src="images/1bhk.avif" class="card-img-top" alt="1BHK">
        <div class="card-body">
          <h5 class="card-title">1 BHK Apartment</h5>
          <p class="card-text">Perfect for individuals or small families, with modern facilities and comfort.</p>
          <p class="text-muted">Floor Area: 750 sq.ft</p>
        </div>
      </div>
    </div>
    <div class="col-md-4">
      <div class="card flat-card shadow-sm">
        <img src="images/2bhk.avif" class="card-img-top" alt="2BHK">
        <div class="card-body">
          <h5 class="card-title">2 BHK Apartment</h5>
          <p class="card-text">Spacious homes with a beautiful balcony view and high-quality interiors.</p>
          <p class="text-muted">Floor Area: 1200 sq.ft</p>
        </div>
      </div>
    </div>
    <div class="col-md-4">
      <div class="card flat-card shadow-sm">
        <img src="images/3bhk.avif" class="card-img-top" alt="3BHK">
        <div class="card-body">
          <h5 class="card-title">3 BHK Apartment</h5>
          <p class="card-text">Luxury and comfort for families who want the best of community living.</p>
          <p class="text-muted">Floor Area: 1800 sq.ft</p>
        </div>
      </div>
    </div>
  </div>
</div>

<footer class="text-center py-4 bg-dark text-white">
  <p class="mb-0">&copy; <?php echo date('Y'); ?> Aravind Sarovar. All Rights Reserved.</p>
</footer>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
