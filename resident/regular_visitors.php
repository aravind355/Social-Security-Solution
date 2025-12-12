<?php
session_start();
include('../includes/db_connect.php');

if (!isset($_SESSION['role']) || $_SESSION['role'] != 'Resident') {
  header("Location: ../login.php");
  exit();
}

$username = $_SESSION['username'];
$resident = $conn->query("
  SELECT r.resident_id, r.flat_id, f.flat_number 
  FROM resident_details r
  JOIN flat_details f ON r.flat_id=f.flat_id
  WHERE f.flat_number='$username'
")->fetch_assoc();

$flat_id = $resident['flat_id'];
$flat_number = $resident['flat_number'];

$helpers = $conn->query("
  SELECT r.name, r.type, r.security_code, l.checkin_time, l.checkout_time
  FROM regular_visitors r
  LEFT JOIN regular_visitor_log l 
    ON r.regular_id = l.regular_id 
    AND DATE(l.checkin_time) = CURDATE()
  WHERE r.flat_id = $flat_id
  ORDER BY r.type
");
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Regular Visitors | Aravind Sarovar</title>
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
.card, .table {
  background: rgba(255,255,255,0.05);
  border: 1px solid gold;
  color: white;
}
.table th { color: gold; }
.btn-gold {
  background: linear-gradient(45deg,#FFD700,#FFB800);
  color:#001a33; font-weight:bold; border-radius:8px;
}
.btn-gold:hover { background: linear-gradient(45deg,#FFEA00,#FFD700); color:#001f3f; }
.badge { font-size: 0.9rem; }
</style>
</head>
<body>

<nav class="navbar navbar-expand-lg navbar-dark">
  <div class="container-fluid">
    <a class="navbar-brand" href="dashboard.php">← Back to Dashboard</a>
    <div class="d-flex">
      <span class="navbar-text text-gold me-3 fw-bold"><?= strtoupper($flat_number) ?></span>
      <a href="../logout.php" class="btn btn-sm btn-outline-warning">Logout</a>
    </div>
  </div>
</nav>

<div class="container mt-4">
  <h2>👥 Regular Helpers & Vendors</h2>

  <div class="table-responsive mt-4">
    <table class="table table-hover text-center align-middle">
      <thead>
        <tr>
          <th>Name</th>
          <th>Role</th>
          <th>Security Code</th>
          <th>Check-In</th>
          <th>Check-Out</th>
          <th>Status</th>
        </tr>
      </thead>
      <tbody>
        <?php
        if ($helpers->num_rows > 0) {
          while ($row = $helpers->fetch_assoc()) {
            $status = 'Not Arrived';
            $badge = 'secondary';
            if ($row['checkin_time'] && !$row['checkout_time']) {
              $status = 'Inside Premises';
              $badge = 'warning';
            } elseif ($row['checkout_time']) {
              $status = 'Checked Out';
              $badge = 'success';
            }

            echo "<tr>
              <td>{$row['name']}</td>
              <td>{$row['type']}</td>
              <td><span class='badge bg-info text-dark'>{$row['security_code']}</span></td>
              <td>" . ($row['checkin_time'] ? date('h:i A', strtotime($row['checkin_time'])) : '-') . "</td>
              <td>" . ($row['checkout_time'] ? date('h:i A', strtotime($row['checkout_time'])) : '-') . "</td>
              <td><span class='badge bg-$badge'>$status</span></td>
            </tr>";
          }
        } else {
          echo "<tr><td colspan='6' class='text-warning'>No regular helpers assigned to your flat.</td></tr>";
        }
        ?>
      </tbody>
    </table>
  </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>