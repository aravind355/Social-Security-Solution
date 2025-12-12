<?php
session_start();
include('../includes/db_connect.php');

if (!isset($_SESSION['role']) || $_SESSION['role'] != 'Supervisor') {
    header("Location: ../login.php");
    exit();
}

$today = date('Y-m-d');

$totalVisitors = $conn->query("SELECT COUNT(*) AS count FROM visitor_details WHERE DATE(checkin_time) = '$today'")->fetch_assoc()['count'];
$approvedVisitors = $conn->query("SELECT COUNT(*) AS count FROM visitor_details WHERE DATE(checkin_time) = '$today' AND status = 'Approved'")->fetch_assoc()['count'];
$pendingVisitors = $conn->query("SELECT COUNT(*) AS count FROM visitor_details WHERE DATE(checkin_time) = '$today' AND status = 'Pending'")->fetch_assoc()['count'];
$activeStaff = $conn->query("SELECT COUNT(*) AS count FROM staff_details")->fetch_assoc()['count'];
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Supervisor Dashboard | Aravind Sarovar</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
<style>
body {
  font-family: 'Segoe UI', sans-serif;
  background: linear-gradient(135deg, #001a33, #002b50);
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
  letter-spacing: 0.5px;
}
h2 {
  color: gold;
  text-align: center;
  margin: 30px 0;
}
.card {
  background: rgba(255, 255, 255, 0.1);
  border: 1px solid gold;
  color: white;
  box-shadow: 0 0 10px rgba(255, 215, 0, 0.2);
  transition: all 0.3s ease;
}
.card:hover {
  transform: translateY(-5px);
  box-shadow: 0 0 20px gold;
}
.card-title {
  color: gold;
  font-weight: bold;
  text-transform: uppercase;
}
.btn-gold {
  background: linear-gradient(45deg, #FFD700, #FFB800);
  color: #001a33;
  font-weight: bold;
  border-radius: 8px;
  box-shadow: 0 0 10px rgba(255, 215, 0, 0.4);
  transition: all 0.3s ease;
}
.btn-gold:hover {
  background: linear-gradient(45deg, #FFEA00, #FFD700);
  box-shadow: 0 0 20px gold;
  transform: translateY(-2px);
  color: #001f3f;
}
.footer {
  text-align: center;
  margin-top: 60px;
  padding: 15px;
  font-size: 14px;
  border-top: 1px solid gold;
  color: gold;
}
</style>
</head>
<body>

<nav class="navbar navbar-expand-lg navbar-dark">
  <div class="container-fluid">
    <a class="navbar-brand" href="#">🏢 Aravind Sarovar Supervisor Panel</a>
    <div class="d-flex">
      <span class="navbar-text text-gold me-3">Role: Supervisor</span>
      <a href="../logout.php" class="btn btn-sm btn-outline-warning">Logout</a>
    </div>
  </div>
</nav>

<div class="container mt-5">
  <h2>📊 Supervisor Dashboard</h2>

  <div class="row g-4 text-center">
    <div class="col-md-3">
      <div class="card p-4">
        <h4 class="card-title">Visitors Today</h4>
        <h2><?= $totalVisitors ?></h2>
      </div>
    </div>
    <div class="col-md-3">
      <div class="card p-4">
        <h4 class="card-title">Approved</h4>
        <h2><?= $approvedVisitors ?></h2>
      </div>
    </div>
    <div class="col-md-3">
      <div class="card p-4">
        <h4 class="card-title">Pending</h4>
        <h2><?= $pendingVisitors ?></h2>
      </div>
    </div>
    <div class="col-md-3">
      <div class="card p-4">
        <h4 class="card-title">Active Staff</h4>
        <h2><?= $activeStaff ?></h2>
      </div>
    </div>
  </div>

  <hr class="text-warning my-5">

  <div class="row text-center">
    <div class="col-md-4">
      <a href="normal_visitors.php" class="btn btn-gold w-100 p-3">🚶 Manage Normal Visitors</a>
    </div>
    <div class="col-md-4">
      <a href="regular_visitors.php" class="btn btn-gold w-100 p-3">🧾 Regular Visitors & Vendors</a>
    </div>
    <div class="col-md-4">
      <a href="staff.php" class="btn btn-gold w-100 p-3">👷 Manage Staff</a>
    </div>
  </div>
</div>

<div class="footer">
  © <?= date('Y') ?> Aravind Sarovar Society | Supervisor Panel
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