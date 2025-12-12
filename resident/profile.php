<?php
session_start();
include('../includes/db_connect.php');

if (!isset($_SESSION['role']) || $_SESSION['role'] != 'Resident') {
  header("Location: ../login.php");
  exit();
}

$username = $_SESSION['username'];
$resident = $conn->query("
  SELECT r.resident_id, r.name, r.email, r.phone, f.flat_id, f.flat_number, f.flat_type, b.building_name
  FROM resident_details r
  JOIN flat_details f ON r.flat_id = f.flat_id
  JOIN building b ON f.building_id = b.building_id
  WHERE f.flat_number = '$username'
")->fetch_assoc();

$flat_id = $resident['flat_id'];
$flat_number = $resident['flat_number'];
$building_name = $resident['building_name'];
$flat_type = $resident['flat_type'];

$family = $conn->query("SELECT member_name, relation, age, phone FROM family_members WHERE flat_id = $flat_id");
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Profile | Aravind Sarovar</title>
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
  box-shadow: 0 0 10px rgba(255,215,0,0.2);
}
.card h5 { color: gold; }
.table { background: rgba(255,255,255,0.05); border: 1px solid gold; color: white; }
.table th { color: gold; }
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
  <h2>👤 My Profile</h2>

  <div class="row g-4 mb-4">
    <div class="col-md-6">
      <div class="card p-4">
        <h5>Resident Details</h5>
        <p><strong>Name:</strong> <?= htmlspecialchars($resident['name']) ?></p>
        <p><strong>Email:</strong> <?= htmlspecialchars($resident['email']) ?></p>
        <p><strong>Phone:</strong> <?= htmlspecialchars($resident['phone']) ?></p>
      </div>
    </div>

    <div class="col-md-6">
      <div class="card p-4">
        <h5>Flat Information</h5>
        <p><strong>Building:</strong> <?= htmlspecialchars($building_name) ?></p>
        <p><strong>Flat Number:</strong> <?= htmlspecialchars($flat_number) ?></p>
        <p><strong>Flat Type:</strong> <?= htmlspecialchars($flat_type) ?></p>
      </div>
    </div>
  </div>

  <div class="card p-4">
    <h5>👨‍👩‍👧 Family Members</h5>
    <div class="table-responsive mt-3">
      <table class="table table-hover text-center align-middle">
        <thead>
          <tr>
            <th>Name</th>
            <th>Relation</th>
            <th>Age</th>
            <th>Phone</th>
          </tr>
        </thead>
        <tbody>
          <?php
          if ($family->num_rows > 0) {
            while ($row = $family->fetch_assoc()) {
              echo "<tr>
                <td>{$row['member_name']}</td>
                <td>{$row['relation']}</td>
                <td>{$row['age']}</td>
                <td>" . ($row['phone'] ?: '-') . "</td>
              </tr>";
            }
          } else {
            echo "<tr><td colspan='4' class='text-warning'>No family members listed.</td></tr>";
          }
          ?>
        </tbody>
      </table>
    </div>
  </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>