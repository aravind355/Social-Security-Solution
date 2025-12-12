<?php
session_start();
include('../includes/db_connect.php');

if (!isset($_SESSION['role']) || $_SESSION['role'] != 'Resident') {
  header("Location: ../login.php");
  exit();
}

$username = $_SESSION['username'];
$resident = $conn->query("
  SELECT r.resident_id, r.name, r.flat_id, f.flat_number, f.flat_type
  FROM resident_details r
  JOIN flat_details f ON r.flat_id=f.flat_id
  WHERE f.flat_number='$username'
")->fetch_assoc();

$flat_id = $resident['flat_id'];
$name = $resident['name'];
$flat_number = $resident['flat_number'];
$flat_type = $resident['flat_type'];

$total_pending = $conn->query("SELECT COUNT(*) c FROM visitor_details WHERE flat_id=$flat_id AND status='Pending'")->fetch_assoc()['c'];
$total_today = $conn->query("SELECT COUNT(*) c FROM visitor_details WHERE flat_id=$flat_id AND DATE(checkin_time)=CURDATE() AND status='Approved'")->fetch_assoc()['c'];
$maintenance = $conn->query("SELECT status FROM maintenance_details WHERE flat_id=$flat_id")->fetch_assoc()['status'] ?? 'Due';
$helpers_today = $conn->query("
  SELECT COUNT(*) c 
  FROM regular_visitor_log l
  JOIN regular_visitors r ON l.regular_id=r.regular_id
  WHERE r.flat_id=$flat_id AND DATE(l.checkin_time)=CURDATE()
")->fetch_assoc()['c'];
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Resident Dashboard | Aravind Sarovar</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
<style>
body {
  font-family: 'Segoe UI', sans-serif;
  background: linear-gradient(135deg, #001a33, #002b50);
  color: white;
  min-height: 100vh;
}
.navbar { background:#001a33; border-bottom:2px solid gold; }
.navbar-brand { color:gold!important; font-weight:bold; }
h2 { color:gold; margin:30px 0; text-align:center; }
.card {
  background: rgba(255,255,255,0.05);
  border: 1px solid gold;
  color: white;
  text-align: center;
  box-shadow: 0 0 10px rgba(255,215,0,0.2);
  transition: 0.3s;
}
.card:hover { transform: scale(1.03); }
.card h3 { color: gold; }
.btn-gold {
  background: linear-gradient(45deg,#FFD700,#FFB800);
  color:#001a33; font-weight:bold; border-radius:8px;
}
.btn-gold:hover { background: linear-gradient(45deg,#FFEA00,#FFD700); color:#001f3f; }
</style>
</head>
<body>

<nav class="navbar navbar-expand-lg navbar-dark">
  <div class="container-fluid">
    <span class="navbar-brand">Aravind Sarovar</span>
    <div class="d-flex">
      <span class="navbar-text text-gold me-3 fw-bold"><?= strtoupper($flat_number) ?> (<?= $flat_type ?>)</span>
      <a href="../logout.php" class="btn btn-sm btn-outline-warning">Logout</a>
    </div>
  </div>
</nav>

<div class="container mt-4">
  <h2>Welcome, <?= htmlspecialchars($name) ?> 👋</h2>

  <div class="row g-4 mb-4 text-center">
    <div class="col-md-3">
      <div class="card p-3">
        <h5>Pending Visitors</h5>
        <h3><?= $total_pending ?></h3>
        <a href="visitor_requests.php" class="btn btn-gold mt-2 w-75">View Requests</a>
      </div>
    </div>
    <div class="col-md-3">
      <div class="card p-3">
        <h5>Visitors Today</h5>
        <h3><?= $total_today ?></h3>
        <a href="visitor_history.php" class="btn btn-gold mt-2 w-75">View History</a>
      </div>
    </div>
    <div class="col-md-3">
      <div class="card p-3">
        <h5>Regular Helpers</h5>
        <h3><?= $helpers_today ?></h3>
        <a href="regular_visitors.php" class="btn btn-gold mt-2 w-75">View Helpers</a>
      </div>
    </div>
    <div class="col-md-3">
      <div class="card p-3">
        <h5>Maintenance Status</h5>
        <h3 class="<?= $maintenance=='Paid'?'text-success':'text-danger' ?>"><?= $maintenance ?></h3>
        <a href="maintenance.php" class="btn btn-gold mt-2 w-75">View Details</a>
      </div>
    </div>
  </div>

  <div class="text-center mt-4">
    <a href="profile.php" class="btn btn-outline-warning px-4">👤 View Profile</a>
  </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>