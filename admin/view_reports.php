<?php
session_start();
include('../includes/db_connect.php');

if (!isset($_SESSION['role']) || ($_SESSION['role'] != 'Admin' && $_SESSION['role'] != 'Supervisor')) {
    header("Location: ../login.php");
    exit();
}

$reportType = $_POST['report_type'] ?? '';
$data = [];

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    switch ($reportType) {
        case 'residents':
            $query = "
                SELECT r.name AS resident_name, r.email, r.phone, f.flat_number, b.building_name
                FROM resident_details r
                JOIN flat_details f ON r.flat_id = f.flat_id
                JOIN building b ON f.building_id = b.building_id
                ORDER BY b.building_name, f.flat_number
            ";
            $data = $conn->query($query);
            break;

        case 'visitors':
            $query = "
                SELECT v.visitor_name, f.flat_number, v.checkin_time, v.checkout_time, v.status
                FROM visitor_details v
                JOIN flat_details f ON v.flat_id = f.flat_id
                ORDER BY v.checkin_time DESC
            ";
            $data = $conn->query($query);
            break;

        case 'maintenance':
            $query = "
                SELECT f.flat_number, r.name AS resident_name, m.amount, m.status, m.payment_date, m.mode
                FROM maintenance_details m
                JOIN resident_details r ON m.flat_id = r.flat_id
                JOIN flat_details f ON m.flat_id = f.flat_id
                ORDER BY m.payment_date DESC
            ";
            $data = $conn->query($query);
            break;

        case 'staff':
            $query = "
                SELECT s.name AS staff_name, s.designation AS designation, s.phone, s.shift_time
                FROM staff_details s
                ORDER BY s.designation
            ";
            $data = $conn->query($query);
            break;
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>View Reports | Aravind Sarovar</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
<style>
body {
  font-family: 'Segoe UI', sans-serif;
  background: linear-gradient(to right, #001f3f, #003366);
  color: white;
  min-height: 100vh;
}
.container {
  margin-top: 50px;
}
h2 {
  color: gold;
  text-align: center;
  margin-bottom: 30px;
}
.form-select, .btn-gold {
  border: 1px solid gold;
  background: rgba(255,255,255,0.1);
  color: gold;
}
.btn-gold {
  background: linear-gradient(45deg, #FFD700, #FFB800);
  color: #003366;
  font-weight: bold;
  border-radius: 8px;
  box-shadow: 0 0 10px rgba(255, 215, 0, 0.5);
  transition: all 0.3s ease;
}
.btn-gold:hover {
  background: linear-gradient(45deg, #FFEA00, #FFD700);
  box-shadow: 0 0 20px gold, 0 0 40px gold;
  transform: translateY(-2px);
  color: #001f3f;
}
.table {
  color: white;
  background: rgba(255,255,255,0.1);
  border: 1px solid gold;
  border-radius: 10px;
  margin-top: 30px;
}
.table th {
  color: gold;
  text-transform: uppercase;
}
.navbar {
  background-color: #001a33;
  border-bottom: 2px solid gold;
}
.navbar-brand {
  color: gold !important;
  font-weight: bold;
}
.alert {
  background: rgba(255,215,0,0.1);
  border: 1px solid gold;
  color: gold;
}
</style>
</head>
<body>

<nav class="navbar navbar-expand-lg navbar-dark">
  <div class="container-fluid">
    <a class="navbar-brand" href="dashboard.php">← Back to Dashboard</a>
  </div>
</nav>

<div class="container">
  <h2>📊 View Reports</h2>

  <form method="POST" class="text-center mb-4">
    <div class="row justify-content-center">
      <div class="col-md-4">
        <select name="report_type" class="form-select" required>
          <option value="">Select Report Type</option>
          <option value="residents" <?= ($reportType == 'residents') ? 'selected' : '' ?>>Residents Report</option>
          <option value="visitors" <?= ($reportType == 'visitors') ? 'selected' : '' ?>>Visitors Report</option>
          <option value="maintenance" <?= ($reportType == 'maintenance') ? 'selected' : '' ?>>Maintenance Report</option>
          <option value="staff" <?= ($reportType == 'staff') ? 'selected' : '' ?>>Staff Report</option>
        </select>
      </div>
      <div class="col-md-2">
        <button type="submit" class="btn btn-gold w-100">Generate</button>
      </div>
    </div>
  </form>

  <?php if ($_SERVER['REQUEST_METHOD'] == 'POST' && $data && $data->num_rows > 0): ?>
    <div class="table-responsive">
      <table class="table table-hover text-center align-middle">
        <thead>
          <tr>
            <?php while ($field = $data->fetch_field()): ?>
              <th><?= ucfirst(str_replace('_', ' ', $field->name)) ?></th>
            <?php endwhile; ?>
          </tr>
        </thead>
        <tbody>
          <?php $data->data_seek(0); while ($row = $data->fetch_assoc()): ?>
            <tr>
              <?php foreach ($row as $value): ?>
                <td><?= htmlspecialchars($value) ?></td>
              <?php endforeach; ?>
            </tr>
          <?php endwhile; ?>
        </tbody>
      </table>
    </div>
  <?php elseif ($_SERVER['REQUEST_METHOD'] == 'POST'): ?>
    <div class="alert text-center mt-3">⚠️ No records found for the selected report.</div>
  <?php endif; ?>
</div>

</body>
</html>