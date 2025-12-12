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

$selected_date = $_GET['date'] ?? date('Y-m-d');
if ($selected_date > date('Y-m-d')) $selected_date = date('Y-m-d');

$result = $conn->query("
  SELECT v.visitor_name, v.status, v.checkin_time, v.checkout_time
  FROM visitor_details v
  WHERE v.flat_id=$flat_id 
  AND DATE(v.checkin_time)='$selected_date' 
  AND v.status IN ('Approved','Denied','Cancelled')
  ORDER BY v.checkin_time DESC
");
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Visitor History | Aravind Sarovar</title>
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
  <h2>📋 Visitor History</h2>

  <form method="GET" class="mb-4 text-start">
    <label class="text-warning fw-bold">Select Date:</label>
    <input type="date" name="date" max="<?= date('Y-m-d') ?>" value="<?= $selected_date ?>" onchange="this.form.submit()">
  </form>

  <div class="table-responsive mt-4">
    <table class="table table-hover text-center align-middle">
      <thead>
        <tr>
          <th>Visitor Name</th>
          <th>Check-In</th>
          <th>Check-Out</th>
          <th>Status</th>
        </tr>
      </thead>
      <tbody>
        <?php
        if ($result->num_rows > 0) {
          while ($row = $result->fetch_assoc()) {
            $statusColor = match($row['status']) {
              'Approved' => 'text-success',
              'Denied' => 'text-danger',
              'Cancelled' => 'text-secondary',
              default => 'text-light'
            };
            echo "<tr>
              <td>{$row['visitor_name']}</td>
              <td>" . date("d-m-Y h:i A", strtotime($row['checkin_time'])) . "</td>
              <td>" . ($row['checkout_time'] ? date("d-m-Y h:i A", strtotime($row['checkout_time'])) : '-') . "</td>
              <td class='$statusColor fw-bold'>{$row['status']}</td>
            </tr>";
          }
        } else {
          echo "<tr><td colspan='4' class='text-warning'>No visitors found for this date.</td></tr>";
        }
        ?>
      </tbody>
    </table>
  </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>