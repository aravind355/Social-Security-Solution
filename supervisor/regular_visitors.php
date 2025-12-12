<?php
session_start();
include('../includes/db_connect.php');

if (!isset($_SESSION['role']) || $_SESSION['role'] != 'Supervisor') {
  header("Location: ../login.php");
  exit();
}

if (isset($_POST['add_visitor'])) {
  $name = trim($_POST['name']);
  $type = $_POST['type'];
  $flat_id = intval($_POST['flat_id']);
  $num = $conn->query("SELECT COUNT(*) c FROM regular_visitors")->fetch_assoc()['c'] + 1;
  $code = "RV" . str_pad($num, 3, "0", STR_PAD_LEFT);
  $conn->query("INSERT INTO regular_visitors(name, type, flat_id, security_code)
                VALUES('$name', '$type', $flat_id, '$code')");
  header("Location: regular_visitors.php?success=added");
  exit();
}

if (isset($_POST['update'])) {
  $id = intval($_POST['id']);
  $name = $_POST['name'];
  $type = $_POST['type'];
  $flat_id = intval($_POST['flat_id']);
  $conn->query("UPDATE regular_visitors
                SET name='$name', type='$type', flat_id=$flat_id
                WHERE regular_id=$id");
  header("Location: regular_visitors.php?success=updated");
  exit();
}

if (isset($_POST['delete'])) {
  $id = intval($_POST['delete']);
  $conn->query("DELETE FROM regular_visitors WHERE regular_id=$id");
  header("Location: regular_visitors.php?success=deleted");
  exit();
}

if (isset($_POST['checkin'])) {
  $code = strtoupper(trim($_POST['security_code']));
  $get = $conn->prepare("SELECT regular_id FROM regular_visitors WHERE UPPER(security_code)=?");
  $get->bind_param("s", $code);
  $get->execute();
  $res = $get->get_result();
  if ($res->num_rows > 0) {
    $rid = $res->fetch_assoc()['regular_id'];
    $chk = $conn->query("SELECT * FROM regular_visitor_log WHERE regular_id=$rid AND checkout_time IS NULL");
    if ($chk->num_rows == 0) {
      $conn->query("INSERT INTO regular_visitor_log(regular_id,checkin_time) VALUES($rid,NOW())");
      header("Location: regular_visitors.php?success=checkin");
      exit();
    } else {
      header("Location: regular_visitors.php?success=duplicate");
      exit();
    }
  } else {
    header("Location: regular_visitors.php?success=invalid");
    exit();
  }
}

if (isset($_POST['checkout'])) {
  $id = intval($_POST['checkout']);
  $conn->query("UPDATE regular_visitor_log SET checkout_time=NOW() WHERE log_id=$id");
  header("Location: regular_visitors.php?success=checkout");
  exit();
}

$visitors = $conn->query("
  SELECT r.regular_id, r.name, r.type, f.flat_number, r.security_code
  FROM regular_visitors r
  JOIN flat_details f ON r.flat_id=f.flat_id
  ORDER BY r.regular_id DESC
");

$log = $conn->query("
  SELECT l.log_id, r.name, r.security_code, f.flat_number,
         l.checkin_time, l.checkout_time
  FROM regular_visitor_log l
  JOIN regular_visitors r ON l.regular_id=r.regular_id
  JOIN flat_details f ON r.flat_id=f.flat_id
  ORDER BY l.checkin_time DESC
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
.card, .table { background:rgba(255,255,255,0.05); border:1px solid gold; color:white; }
.table th { color:gold; }
.btn-gold {
  background:linear-gradient(45deg,#FFD700,#FFB800);
  color:#001a33; font-weight:bold; border-radius:8px;
}
.btn-gold:hover { background:linear-gradient(45deg,#FFEA00,#FFD700); color:#001f3f; }
.nav-tabs .nav-link.active { background-color:gold; color:#001a33!important; font-weight:bold; }
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
<h2>👥 Regular Visitors & Vendors</h2>

<?php if(isset($_GET['success'])): ?>
<div class="alert text-center">
  <?php
    if($_GET['success']=='added') echo "Visitor Added Successfully.";
    elseif($_GET['success']=='updated') echo "Visitor Updated Successfully.";
    elseif($_GET['success']=='deleted') echo "Visitor Deleted Successfully.";
    elseif($_GET['success']=='checkin') echo "Check-In Recorded.";
    elseif($_GET['success']=='checkout') echo "Check-Out Recorded.";
    elseif($_GET['success']=='duplicate') echo "Already Checked In.";
    elseif($_GET['success']=='invalid') echo "Invalid Security Code.";
  ?>
</div>
<?php endif; ?>

<ul class="nav nav-tabs mb-4" id="myTab" role="tablist">
  <li class="nav-item" role="presentation">
    <button class="nav-link active" id="manage-tab" data-bs-toggle="tab" data-bs-target="#manage" type="button">Manage Regular Visitors</button>
  </li>
  <li class="nav-item" role="presentation">
    <button class="nav-link" id="log-tab" data-bs-toggle="tab" data-bs-target="#log" type="button">Check-In / Check-Out</button>
  </li>
</ul>

<div class="tab-content" id="myTabContent">

<div class="tab-pane fade show active" id="manage" role="tabpanel">
  <form method="POST" class="row g-3 align-items-end mb-4">
    <div class="col-md-3">
      <label class="form-label text-warning">Name</label>
      <input type="text" name="name" class="form-control" required>
    </div>
    <div class="col-md-3">
      <label class="form-label text-warning">Type</label>
      <select name="type" class="form-select" required>
        <option value="">--Select--</option>
        <option>Maid</option><option>Cook</option><option>Gardener</option>
        <option>Milk Vendor</option><option>Driver</option><option>Other</option>
      </select>
    </div>
    <div class="col-md-3">
      <label class="form-label text-warning">Flat</label>
      <select name="flat_id" class="form-select" required>
        <option value="">--Select Flat--</option>
        <?php
        $flats = $conn->query("SELECT flat_id,flat_number FROM flat_details ORDER BY flat_number");
        while($f=$flats->fetch_assoc()) echo "<option value='{$f['flat_id']}'>{$f['flat_number']}</option>";
        ?>
      </select>
    </div>
    <div class="col-md-3">
      <button type="submit" name="add_visitor" class="btn btn-gold w-100">Add Visitor</button>
    </div>
  </form>

  <div class="table-responsive">
    <table class="table text-center align-middle">
      <thead><tr>
        <th>Name</th><th>Type</th><th>Flat</th><th>Code</th><th>Actions</th>
      </tr></thead>
      <tbody>
      <?php
      if($visitors->num_rows>0){
        while($v=$visitors->fetch_assoc()){
          echo "<tr>
            <td>{$v['name']}</td>
            <td>{$v['type']}</td>
            <td>{$v['flat_number']}</td>
            <td>{$v['security_code']}</td>
            <td>
              <form method='POST' style='display:inline'>
                <input type='hidden' name='delete' value='{$v['regular_id']}'>
                <button class='btn btn-sm btn-outline-danger'>Delete</button>
              </form>
            </td>
          </tr>";
        }
      } else echo "<tr><td colspan='5' class='text-warning'>No regular visitors added.</td></tr>";
      ?>
      </tbody>
    </table>
  </div>
</div>

<div class="tab-pane fade" id="log" role="tabpanel">
  <form method="POST" class="row g-3 align-items-end mb-4">
    <div class="col-md-4">
      <label class="form-label text-warning">Enter Security Code</label>
      <input type="text" name="security_code" class="form-control" placeholder="RV001" required>
    </div>
    <div class="col-md-2">
      <button type="submit" name="checkin" class="btn btn-gold w-100">Check-In</button>
    </div>
  </form>

  <div class="table-responsive">
    <table class="table text-center align-middle">
      <thead><tr>
        <th>Name</th><th>Flat</th><th>Code</th><th>Check-In</th><th>Check-Out</th><th>Action</th>
      </tr></thead>
      <tbody>
      <?php
      if($log->num_rows>0){
        while($l=$log->fetch_assoc()){
          echo "<tr>
            <td>{$l['name']}</td>
            <td>{$l['flat_number']}</td>
            <td>{$l['security_code']}</td>
            <td>".date('d-m-Y h:i A',strtotime($l['checkin_time']))."</td>
            <td>".($l['checkout_time']?date('d-m-Y h:i A',strtotime($l['checkout_time'])):'-')."</td>
            <td>";
          if(is_null($l['checkout_time'])){
            echo "<form method='POST'>
                    <button class='btn btn-sm btn-outline-warning' name='checkout' value='{$l['log_id']}'>Check-Out</button>
                  </form>";
          } else echo "—";
          echo "</td></tr>";
        }
      } else echo "<tr><td colspan='6' class='text-warning'>No records yet.</td></tr>";
      ?>
      </tbody>
    </table>
  </div>
</div>

</div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script>
document.querySelectorAll('button[data-bs-toggle="tab"]').forEach(btn=>{
  btn.addEventListener('shown.bs.tab',e=>{
    localStorage.setItem('regTab',e.target.getAttribute('data-bs-target'));
  });
});
const active=localStorage.getItem('regTab');
if(active){const el=document.querySelector(`button[data-bs-target="${active}"]`);
  if(el) new bootstrap.Tab(el).show();}
setInterval(()=>{if(document.querySelector('#log').classList.contains('active'))location.reload();},30000);
</script>
</body>
</html>