<?php
session_start();
include('../includes/db_connect.php');

if (!isset($_SESSION['role']) || $_SESSION['role'] != 'Supervisor') {
  header("Location: ../login.php");
  exit();
}

/* ---------- ADD STAFF ---------- */
if (isset($_POST['add_staff'])) {
  $name = trim($_POST['name']);
  $designation = trim($_POST['designation']);
  $phone = trim($_POST['phone']);
  $shift = trim($_POST['shift']);
  $salary = floatval($_POST['salary']);
  $stmt = $conn->prepare("INSERT INTO staff_details(name,designation,phone,shift_time,salary) VALUES(?,?,?,?,?)");
  $stmt->bind_param("ssssd", $name, $designation, $phone, $shift, $salary);
  $stmt->execute();
  header("Location: manage_staff.php?success=added");
  exit();
}

/* ---------- UPDATE STAFF ---------- */
if (isset($_POST['update_staff'])) {
  $id = intval($_POST['id']);
  $name = $_POST['name'];
  $designation = $_POST['designation'];
  $phone = $_POST['phone'];
  $shift = $_POST['shift'];
  $salary = floatval($_POST['salary']);
  $stmt = $conn->prepare("UPDATE staff_details SET name=?, designation=?, phone=?, shift_time=?, salary=? WHERE staff_id=?");
  $stmt->bind_param("ssssdi", $name, $designation, $phone, $shift, $salary, $id);
  $stmt->execute();
  header("Location: manage_staff.php?success=updated");
  exit();
}

/* ---------- DELETE STAFF ---------- */
if (isset($_POST['delete_staff'])) {
  $id = intval($_POST['delete_staff']);
  $conn->query("DELETE FROM staff_details WHERE staff_id=$id");
  header("Location: manage_staff.php?success=deleted");
  exit();
}

/* ---------- FETCH ALL STAFF ---------- */
$staff = $conn->query("SELECT * FROM staff_details ORDER BY staff_id DESC");
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Manage Staff | Aravind Sarovar</title>
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
.card, .table { background:rgba(255,255,255,0.05); border:1px solid gold; color:white; }
.table th { color:gold; }
.btn-gold {
  background:linear-gradient(45deg,#FFD700,#FFB800);
  color:#001a33; font-weight:bold; border-radius:8px;
}
.btn-gold:hover { background:linear-gradient(45deg,#FFEA00,#FFD700); color:#001f3f; }
.alert { background:rgba(255,215,0,0.1); border:1px solid gold; color:gold; }
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
<h2>👷 Manage Staff Details</h2>

<?php if(isset($_GET['success'])): ?>
<div class="alert text-center">
  <?php
    if($_GET['success']=='added') echo "Staff Added Successfully.";
    elseif($_GET['success']=='updated') echo "Staff Updated Successfully.";
    elseif($_GET['success']=='deleted') echo "Staff Deleted Successfully.";
  ?>
</div>
<?php endif; ?>

<!-- Add Staff Form -->
<form method="POST" class="row g-3 align-items-end mb-4">
  <div class="col-md-3">
    <label class="form-label text-warning">Name</label>
    <input type="text" name="name" class="form-control" required>
  </div>
  <div class="col-md-3">
    <label class="form-label text-warning">Designation</label>
    <input type="text" name="designation" class="form-control" required>
  </div>
  <div class="col-md-2">
    <label class="form-label text-warning">Phone</label>
    <input type="text" name="phone" class="form-control" required pattern="[0-9]{10}">
  </div>
  <div class="col-md-2">
    <label class="form-label text-warning">Shift</label>
    <input type="text" name="shift" class="form-control" placeholder="9AM - 6PM" required>
  </div>
  <div class="col-md-2">
    <label class="form-label text-warning">Salary</label>
    <input type="number" name="salary" step="0.01" class="form-control" required>
  </div>
  <div class="col-md-12 text-center">
    <button type="submit" name="add_staff" class="btn btn-gold w-25">Add Staff</button>
  </div>
</form>

<!-- Staff Table -->
<div class="table-responsive mt-4">
  <table class="table table-hover text-center align-middle">
    <thead>
      <tr>
        <th>Name</th>
        <th>Designation</th>
        <th>Phone</th>
        <th>Shift</th>
        <th>Salary</th>
        <th>Actions</th>
      </tr>
    </thead>
    <tbody>
      <?php
      if($staff->num_rows>0){
        while($row=$staff->fetch_assoc()){
          echo "<tr>
            <td>{$row['name']}</td>
            <td>{$row['designation']}</td>
            <td>{$row['phone']}</td>
            <td>{$row['shift_time']}</td>
            <td>₹{$row['salary']}</td>
            <td>
              <button class='btn btn-sm btn-outline-warning' data-bs-toggle='modal' data-bs-target='#editModal{$row['staff_id']}'>Edit</button>
              <form method='POST' style='display:inline'>
                <input type='hidden' name='delete_staff' value='{$row['staff_id']}'>
                <button class='btn btn-sm btn-outline-danger'>Delete</button>
              </form>
            </td>
          </tr>";

          // Edit Modal
          echo "
          <div class='modal fade' id='editModal{$row['staff_id']}' tabindex='-1'>
            <div class='modal-dialog modal-dialog-centered'>
              <div class='modal-content text-dark'>
                <div class='modal-header'>
                  <h5 class='modal-title'>Edit Staff</h5>
                  <button type='button' class='btn-close' data-bs-dismiss='modal'></button>
                </div>
                <form method='POST'>
                <div class='modal-body'>
                  <input type='hidden' name='id' value='{$row['staff_id']}'>
                  <div class='mb-3'>
                    <label>Name</label>
                    <input type='text' name='name' class='form-control' value='{$row['name']}' required>
                  </div>
                  <div class='mb-3'>
                    <label>Designation</label>
                    <input type='text' name='designation' class='form-control' value='{$row['designation']}' required>
                  </div>
                  <div class='mb-3'>
                    <label>Phone</label>
                    <input type='text' name='phone' class='form-control' value='{$row['phone']}' required>
                  </div>
                  <div class='mb-3'>
                    <label>Shift</label>
                    <input type='text' name='shift' class='form-control' value='{$row['shift_time']}' required>
                  </div>
                  <div class='mb-3'>
                    <label>Salary</label>
                    <input type='number' step='0.01' name='salary' class='form-control' value='{$row['salary']}' required>
                  </div>
                </div>
                <div class='modal-footer'>
                  <button type='submit' name='update_staff' class='btn btn-gold'>Save Changes</button>
                </div>
                </form>
              </div>
            </div>
          </div>";
        }
      } else {
        echo "<tr><td colspan='6' class='text-warning'>No staff records found.</td></tr>";
      }
      ?>
    </tbody>
  </table>
</div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>