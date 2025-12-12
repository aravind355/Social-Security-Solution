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
$resident_id = $resident['resident_id'];

if (isset($_GET['approve'])) {
  $id = intval($_GET['approve']);
  $conn->query("UPDATE visitor_details SET status='Approved', approved_by=$resident_id WHERE visitor_id=$id");
  header("Location: visitor_requests.php?success=approved");
  exit();
}

if (isset($_GET['deny'])) {
  $id = intval($_GET['deny']);
  $conn->query("UPDATE visitor_details SET status='Denied', approved_by=$resident_id WHERE visitor_id=$id");
  header("Location: visitor_requests.php?success=denied");
  exit();
}

$result = $conn->query("
  SELECT v.visitor_id, v.visitor_name, f.flat_number, v.checkin_time, v.status
  FROM visitor_details v
  JOIN flat_details f ON v.flat_id=f.flat_id
  WHERE v.flat_id=$flat_id AND v.status='Pending'
  ORDER BY v.checkin_time DESC
");
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Visitor Requests | Aravind Sarovar</title>
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
.alert { background: rgba(255,215,0,0.1); border: 1px solid gold; color: gold; }
</style>
</head>
<body>

<nav class="navbar navbar-expand-lg navbar-dark">
  <div class="container-fluid">
    <a class="navbar-brand" href="dashboard.php">← Back to Dashboard</a>
    <div class="d-flex">
      <span class="navbar-text text-gold me-3 fw-bold"><?= strtoupper($resident['flat_number']) ?></span>
      <a href="../logout.php" class="btn btn-sm btn-outline-warning">Logout</a>
    </div>
  </div>
</nav>

<div class="container mt-4">
  <h2>🚪 Pending Visitor Requests</h2>

  <?php if(isset($_GET['success'])): ?>
  <div class="alert text-center">
    <?php
      if($_GET['success']=='approved') echo "Visitor Approved Successfully ✅";
      elseif($_GET['success']=='denied') echo "Visitor Denied ❌";
    ?>
  </div>
  <?php endif; ?>

  <div class="table-responsive mt-4">
    <table class="table table-hover text-center align-middle">
      <thead>
        <tr>
          <th>Visitor Name</th>
          <th>Flat</th>
          <th>Check-In Time</th>
          <th>Status</th>
          <th>Action</th>
        </tr>
      </thead>
      <tbody>
        <?php
        if ($result->num_rows > 0) {
          while ($row = $result->fetch_assoc()) {
            echo "<tr>
              <td>{$row['visitor_name']}</td>
              <td>{$row['flat_number']}</td>
              <td>" . date("d-m-Y h:i A", strtotime($row['checkin_time'])) . "</td>
              <td class='text-warning fw-bold'>{$row['status']}</td>
              <td>
                <a href='visitor_requests.php?approve={$row['visitor_id']}' class='btn btn-sm btn-success me-2'>Approve</a>
                <a href='visitor_requests.php?deny={$row['visitor_id']}' class='btn btn-sm btn-danger'>Deny</a>
              </td>
            </tr>";
          }
        } else {
          echo "<tr><td colspan='5' class='text-warning'>No pending visitors for your flat.</td></tr>";
        }
        ?>
      </tbody>
    </table>
  </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>