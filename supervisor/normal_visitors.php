<?php
session_start();
include('../includes/db_connect.php');

if (!isset($_SESSION['role']) || $_SESSION['role'] != 'Supervisor') {
  header("Location: ../login.php");
  exit();
}

$selected_date = $_GET['date'] ?? date('Y-m-d');
if ($selected_date > date('Y-m-d')) $selected_date = date('Y-m-d');

if (isset($_POST['add_visitor'])) {
  $name = trim($_POST['visitor_name']);
  $flat_id = intval($_POST['flat_id']);
  $stmt = $conn->prepare("INSERT INTO visitor_details (visitor_name, flat_id, checkin_time, status) VALUES (?, ?, NOW(), 'Pending')");
  $stmt->bind_param("si", $name, $flat_id);
  $stmt->execute();
  header("Location: normal_visitors.php?date=$selected_date");
  exit();
}

if (isset($_GET['checkout_id'])) {
  $id = intval($_GET['checkout_id']);
  $status_check = $conn->query("SELECT status FROM visitor_details WHERE visitor_id=$id")->fetch_assoc();
  $status = $status_check['status'];
  if ($status != 'Approved') {
    $conn->query("UPDATE visitor_details SET checkout_time=NOW(), status='Cancelled' WHERE visitor_id=$id");
  } else {
    $conn->query("UPDATE visitor_details SET checkout_time=NOW() WHERE visitor_id=$id");
  }
  header("Location: normal_visitors.php?date=$selected_date");
  exit();
}

$query = "
  SELECT v.visitor_id, v.visitor_name, f.flat_number, v.checkin_time, v.checkout_time, v.status
  FROM visitor_details v
  JOIN flat_details f ON v.flat_id = f.flat_id
  WHERE DATE(v.checkin_time) = '$selected_date'
  ORDER BY v.checkin_time DESC
";
$result = $conn->query($query);

$totalVisitors = $conn->query("SELECT COUNT(*) AS c FROM visitor_details WHERE DATE(checkin_time)='$selected_date'")->fetch_assoc()['c'];
$approvedVisitors = $conn->query("SELECT COUNT(*) AS c FROM visitor_details WHERE DATE(checkin_time)='$selected_date' AND status='Approved'")->fetch_assoc()['c'];
$pendingVisitors = $conn->query("SELECT COUNT(*) AS c FROM visitor_details WHERE DATE(checkin_time)='$selected_date' AND status='Pending'")->fetch_assoc()['c'];
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Normal Visitors | Aravind Sarovar</title>
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
.navbar-brand { color: gold !important; font-weight: bold; }
h2 { color: gold; margin: 30px 0; text-align: center; }
.card {
  background: rgba(255, 255, 255, 0.1);
  border: 1px solid gold;
  color: white;
  box-shadow: 0 0 10px rgba(255, 215, 0, 0.2);
}
.table {
  color: white;
  background: rgba(255, 255, 255, 0.05);
  border: 1px solid gold;
}
.table th {
  color: gold;
}
.btn-gold {
  background: linear-gradient(45deg, #FFD700, #FFB800);
  color: #001a33;
  font-weight: bold;
  border-radius: 8px;
}
.btn-gold:hover {
  background: linear-gradient(45deg, #FFEA00, #FFD700);
  color: #001f3f;
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
    <div class="d-flex">
      <span class="navbar-text text-gold me-3">Supervisor Panel</span>
      <a href="../logout.php" class="btn btn-sm btn-outline-warning">Logout</a>
    </div>
  </div>
</nav>

<div class="container mt-4">
  <h2>🚶 Normal Visitor Management</h2>

  <div class="row g-3 text-center mb-4">
    <div class="col-md-4"><div class="card p-3"><h5>Total Visitors</h5><h3><?= $totalVisitors ?></h3></div></div>
    <div class="col-md-4"><div class="card p-3"><h5>Approved</h5><h3><?= $approvedVisitors ?></h3></div></div>
    <div class="col-md-4"><div class="card p-3"><h5>Pending</h5><h3><?= $pendingVisitors ?></h3></div></div>
  </div>

  <form method="GET" class="mb-3 text-start">
    <label class="text-warning fw-bold">Select Date:</label>
    <input type="date" name="date" max="<?= date('Y-m-d') ?>" value="<?= $selected_date ?>" onchange="this.form.submit()">
  </form>

  <form method="POST" class="row g-3 align-items-end mb-4">
    <div class="col-md-5">
      <label class="form-label text-warning">Visitor Name</label>
      <input type="text" name="visitor_name" class="form-control" placeholder="Enter visitor name" required>
    </div>
    <div class="col-md-5">
      <label class="form-label text-warning">Select Flat</label>
      <select name="flat_id" class="form-select" required>
        <option value="">-- Select Flat --</option>
        <?php
        $flats = $conn->query("SELECT flat_id, flat_number FROM flat_details ORDER BY flat_number");
        while ($f = $flats->fetch_assoc()) {
          echo "<option value='{$f['flat_id']}'>{$f['flat_number']}</option>";
        }
        ?>
      </select>
    </div>
    <div class="col-md-2">
      <button type="submit" name="add_visitor" class="btn btn-gold w-100">Add Visitor</button>
    </div>
  </form>

  <div class="table-responsive mt-4">
    <table class="table table-hover text-center align-middle">
      <thead>
        <tr>
          <th>Name</th>
          <th>Flat</th>
          <th>Check-In</th>
          <th>Check-Out</th>
          <th>Status</th>
          <th>Action</th>
        </tr>
      </thead>
      <tbody>
        <?php
        if ($result->num_rows > 0) {
          while ($row = $result->fetch_assoc()) {
            $statusColor = match ($row['status']) {
              'Approved' => 'text-success',
              'Pending' => 'text-warning',
              'Cancelled' => 'text-danger',
              'Denied' => 'text-danger',
              default => 'text-light'
            };
            echo "<tr>
              <td>{$row['visitor_name']}</td>
              <td>{$row['flat_number']}</td>
              <td>" . date("d-m-Y h:i A", strtotime($row['checkin_time'])) . "</td>
              <td>" . ($row['checkout_time'] ? date("d-m-Y h:i A", strtotime($row['checkout_time'])) : '-') . "</td>
              <td class='$statusColor fw-bold'>{$row['status']}</td>";
            if (is_null($row['checkout_time'])) {
              echo "<td><a href='normal_visitors.php?checkout_id={$row['visitor_id']}&date=$selected_date' class='btn btn-sm btn-outline-warning'>Check Out</a></td>";
            } else {
              echo "<td>—</td>";
            }
            echo "</tr>";
          }
        } else {
          echo "<tr><td colspan='6' class='text-warning'>No visitors recorded on this date.</td></tr>";
        }
        ?>
      </tbody>
    </table>
  </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>