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

$maintenance = $conn->query("
  SELECT amount, status, payment_date, mode 
  FROM maintenance_details 
  WHERE flat_id=$flat_id
  ORDER BY maint_id DESC
")->fetch_all(MYSQLI_ASSOC);
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Maintenance Details | Aravind Sarovar</title>
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
  <h2>💰 Maintenance Details</h2>

  <div class="table-responsive mt-4">
    <table class="table table-hover text-center align-middle">
      <thead>
        <tr>
          <th>Amount (₹)</th>
          <th>Status</th>
          <th>Payment Date</th>
          <th>Mode</th>
        </tr>
      </thead>
      <tbody>
        <?php
        if (count($maintenance) > 0) {
          foreach ($maintenance as $row) {
            $statusBadge = $row['status'] == 'Paid' ? 'success' : 'danger';
            echo "<tr>
              <td>{$row['amount']}</td>
              <td><span class='badge bg-$statusBadge'>{$row['status']}</span></td>
              <td>" . ($row['payment_date'] ? date('d-m-Y', strtotime($row['payment_date'])) : '-') . "</td>
              <td>" . ($row['mode'] ?? '-') . "</td>
            </tr>";
          }
        } else {
          echo "<tr><td colspan='4' class='text-warning'>No maintenance records available.</td></tr>";
        }
        ?>
      </tbody>
    </table>
  </div>

  <div class="text-center mt-4">
    <?php
      $latest = $maintenance[0]['status'] ?? 'Due';
      if ($latest == 'Due') {
        echo "<div class='alert alert-warning w-50 mx-auto'>⚠️ Your maintenance payment is due. Please contact the office.</div>";
      } else {
        echo "<div class='alert alert-success w-50 mx-auto'>✅ All maintenance payments are clear!</div>";
      }
    ?>
  </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
