<?php
session_start();
include('../includes/db_connect.php');

if (!isset($_SESSION['role']) || $_SESSION['role'] != 'Admin') {
    header("Location: ../login.php");
    exit();
}

$flats = $conn->query("
    SELECT f.flat_id, f.flat_number, b.building_name 
    FROM flat_details f
    JOIN building b ON f.building_id = b.building_id
    ORDER BY b.building_name, f.flat_number
");

if (isset($_POST['add_user'])) {
    $role = $_POST['role'];
    $username = $_POST['username'];
    $password = password_hash($_POST['password'], PASSWORD_BCRYPT);
    $flat_id = ($_POST['role'] == 'Resident') ? $_POST['flat_id'] : NULL;
    $email = $_POST['email'];
    $phone = $_POST['phone'];
    $name = $_POST['name'];

    $stmt = $conn->prepare("INSERT INTO login_credentials (username, password, role) VALUES (?, ?, ?)");
    $stmt->bind_param("sss", $username, $password, $role);
    $stmt->execute();
    $stmt->close();

    if ($role == 'Resident') {
        $stmt2 = $conn->prepare("INSERT INTO resident_details (flat_id, name, email, phone) VALUES (?, ?, ?, ?)");
        $stmt2->bind_param("isss", $flat_id, $name, $email, $phone);
        $stmt2->execute();
        $stmt2->close();
    }

    header("Location: manage_users.php?success=1");
    exit();
}

if (isset($_GET['delete'])) {
    $id = $_GET['delete'];
    $conn->query("DELETE FROM login_credentials WHERE id = $id");
    header("Location: manage_users.php?deleted=1");
    exit();
}

if (isset($_POST['update_user'])) {
    $id = $_POST['edit_id'];
    $username = $_POST['edit_username'];
    $role = $_POST['edit_role'];
    $name = $_POST['edit_name'];
    $email = $_POST['edit_email'];
    $phone = $_POST['edit_phone'];

    $stmt = $conn->prepare("UPDATE login_credentials SET username=?, role=? WHERE id=?");
    $stmt->bind_param("ssi", $username, $role, $id);
    $stmt->execute();
    $stmt->close();

    if ($role == 'Resident') {
        $conn->query("UPDATE resident_details SET name='$name', email='$email', phone='$phone' WHERE flat_id IN (SELECT flat_id FROM flat_details LIMIT 1)");
    }

    header("Location: manage_users.php?updated=1");
    exit();
}

$result = $conn->query("SELECT * FROM login_credentials ORDER BY id DESC");
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Manage Users | Aravind Sarovar</title>
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
.form-control {
  background: rgba(255, 255, 255, 0.1);
  border: 1px solid gold;
  color: gold;
  font-weight: 500;
}
.form-control::placeholder {
  color: rgba(255, 215, 0, 0.7);
}
.form-control:focus {
  box-shadow: 0 0 8px gold;
  background: rgba(255, 255, 255, 0.2);
  color: gold;
}
.btn-gold {
  background: linear-gradient(45deg, #FFD700, #FFB800);
  color: #003366;
  font-weight: bold;
  border: 2px solid #FFD700;
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
}
.table th {
  color: gold;
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
  <h2>👥 Manage Users</h2>

  <?php if (isset($_GET['success'])): ?>
    <div class="alert text-center">✅ User added successfully!</div>
  <?php elseif (isset($_GET['deleted'])): ?>
    <div class="alert text-center">🗑 User deleted successfully!</div>
  <?php elseif (isset($_GET['updated'])): ?>
    <div class="alert text-center">✏️ User updated successfully!</div>
  <?php endif; ?>

  <form method="POST" class="row g-3 mb-5">
    <div class="col-md-2">
      <select name="role" class="form-control" id="roleSelect" required onchange="toggleFlatSelect()">
        <option value="">Role</option>
        <option>Resident</option>
        <option>Supervisor</option>
      </select>
    </div>
    <div class="col-md-2">
      <input type="text" name="username" class="form-control" placeholder="Username" required>
    </div>
    <div class="col-md-2">
      <input type="password" name="password" class="form-control" placeholder="Password" required>
    </div>
    <div class="col-md-2">
      <input type="text" name="name" class="form-control" placeholder="Full Name" required>
    </div>
    <div class="col-md-2">
      <input type="email" name="email" class="form-control" placeholder="Email" required>
    </div>
    <div class="col-md-2">
      <input type="text" name="phone" class="form-control" placeholder="Phone" required>
    </div>
    <div class="col-md-3 mt-3" id="flatSelectDiv" style="display:none;">
      <select name="flat_id" class="form-control">
        <option value="">Select Flat</option>
        <?php
        $flats = $conn->query("
            SELECT f.flat_id, f.flat_number, b.building_name 
            FROM flat_details f
            JOIN building b ON f.building_id = b.building_id
            ORDER BY b.building_name, f.flat_number
        ");
        while($f = $flats->fetch_assoc()): ?>
          <option value="<?= $f['flat_id'] ?>"><?= $f['building_name'] ?> - <?= $f['flat_number'] ?></option>
        <?php endwhile; ?>
      </select>
    </div>
    <div class="col-md-2 mt-3">
      <button type="submit" name="add_user" class="btn btn-gold w-100">Add User</button>
    </div>
  </form>

  <table class="table table-hover text-center align-middle">
    <thead>
      <tr>
        <th>ID</th>
        <th>Username</th>
        <th>Role</th>
        <th>Actions</th>
      </tr>
    </thead>
    <tbody>
      <?php while($row = $result->fetch_assoc()): ?>
      <tr>
        <td><?= $row['id'] ?></td>
        <td><?= htmlspecialchars($row['username']) ?></td>
        <td><?= htmlspecialchars($row['role']) ?></td>
        <td>
          <button class="btn btn-sm btn-gold"
            data-bs-toggle="modal"
            data-bs-target="#editModal"
            data-id="<?= $row['id'] ?>"
            data-username="<?= htmlspecialchars($row['username']) ?>"
            data-role="<?= htmlspecialchars($row['role']) ?>">
            ✏️ Edit
          </button>
          <a href="?delete=<?= $row['id'] ?>" class="btn btn-danger btn-sm" onclick="return confirm('Delete this user?')">🗑 Delete</a>
        </td>
      </tr>
      <?php endwhile; ?>
    </tbody>
  </table>
</div>

<div class="modal fade" id="editModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content" style="background: rgba(0, 0, 40, 0.95); border: 2px solid gold; color: white;">
      <div class="modal-header">
        <h5 class="modal-title" style="color: gold;">Edit User</h5>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
      </div>
      <form method="POST">
        <div class="modal-body">
          <input type="hidden" name="edit_id" id="edit_id">
          <div class="mb-3">
            <label class="form-label">Username</label>
            <input type="text" class="form-control" name="edit_username" id="edit_username" required>
          </div>
          <div class="mb-3">
            <label class="form-label">Role</label>
            <select class="form-control" name="edit_role" id="edit_role" required>
              <option>Resident</option>
              <option>Supervisor</option>
              <option>Admin</option>
            </select>
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
          <button type="submit" name="update_user" class="btn btn-gold">Save Changes</button>
        </div>
      </form>
    </div>
  </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script>
function toggleFlatSelect() {
  const role = document.getElementById("roleSelect").value;
  document.getElementById("flatSelectDiv").style.display = (role === "Resident") ? "block" : "none";
}

const editModal = document.getElementById('editModal');
editModal.addEventListener('show.bs.modal', event => {
  const button = event.relatedTarget;
  document.getElementById('edit_id').value = button.getAttribute('data-id');
  document.getElementById('edit_username').value = button.getAttribute('data-username');
  document.getElementById('edit_role').value = button.getAttribute('data-role');
});
</script>
</body>
</html>