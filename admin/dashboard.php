<?php
session_start();
include('../includes/db_connect.php');

if (!isset($_SESSION['role']) || $_SESSION['role'] != 'Admin') {
    header("Location: ../login.php");
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Admin Dashboard | Aravind Sarovar</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
<style>
body {
  font-family: 'Segoe UI', sans-serif;
  background: linear-gradient(to right, #001f3f, #003366);
  color: white;
  min-height: 100vh;
}
.navbar {
  background-color: #001a33;
  border-bottom: 2px solid gold;
}
.navbar-brand {
  color: gold !important;
  font-weight: bold;
  font-size: 1.3rem;
}
.card {
  border: 2px solid gold;
  border-radius: 15px;
  transition: all 0.3s ease;
  background: rgba(255, 255, 255, 0.1);
  backdrop-filter: blur(10px);
  color: white;
}
.card:hover {
  transform: translateY(-5px);
  box-shadow: 0 0 15px gold;
}
.card-title {
  color: gold;
  font-weight: 600;
}
.btn-logout {
  background: gold;
  color: #003366;
  font-weight: bold;
  border-radius: 8px;
}
.btn-logout:hover {
  background: #ffcf40;
}
</style>
</head>
<body>
<nav class="navbar navbar-expand-lg navbar-dark">
  <div class="container-fluid">
    <a class="navbar-brand" href="#">Aravind Sarovar - Admin</a>
    <div class="d-flex">
      <form method="post">
        <button name="logout" class="btn btn-logout">Logout</button>
      </form>
    </div>
  </div>
</nav>

<div class="container py-5">
  <h2 class="text-center mb-5" style="color:gold;">Welcome, Administrator</h2>

  <div class="row g-4">
    <div class="col-md-3">
      <a href="manage_buildings.php" style="text-decoration:none;">
        <div class="card p-4 text-center">
          <h5 class="card-title">🏢 Manage Buildings</h5>
          <p>Add or update building information.</p>
        </div>
      </a>
    </div>
    <div class="col-md-3">
      <a href="manage_flats.php" style="text-decoration:none;">
        <div class="card p-4 text-center">
          <h5 class="card-title">🏠 Manage Flats</h5>
          <p>View or edit flats under each building.</p>
        </div>
      </a>
    </div>
    <div class="col-md-3">
      <a href="manage_users.php" style="text-decoration:none;">
        <div class="card p-4 text-center">
          <h5 class="card-title">👥 Manage Users</h5>
          <p>Create accounts for residents & supervisors.</p>
        </div>
      </a>
    </div>
    <div class="col-md-3">
      <a href="view_reports.php" style="text-decoration:none;">
        <div class="card p-4 text-center">
          <h5 class="card-title">📊 View Reports</h5>
          <p>Check maintenance & visitor data.</p>
        </div>
      </a>
    </div>
  </div>
</div>

<?php
if (isset($_POST['logout'])) {
    session_destroy();
    header("Location: ../login.php");
    exit();
}
?>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>