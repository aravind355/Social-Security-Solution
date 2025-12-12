<?php
session_start();
include('../includes/db_connect.php');

if (!isset($_SESSION['role']) || $_SESSION['role'] != 'Admin') {
    header("Location: ../login.php");
    exit();
}

if (isset($_POST['add_building'])) {
    $name = $_POST['building_name'];
    $flats = $_POST['total_flats'];
    $floors = $_POST['total_floors'];
    $address = $_POST['address'];

    $stmt = $conn->prepare("INSERT INTO building (building_name, total_flats, total_floors, address) VALUES (?, ?, ?, ?)");
    $stmt->bind_param("siis", $name, $flats, $floors, $address);
    $stmt->execute();
    $stmt->close();

    header("Location: manage_buildings.php?success=1");
    exit();
}

if (isset($_GET['delete'])) {
    $id = $_GET['delete'];
    $conn->query("DELETE FROM building WHERE building_id = $id");
    header("Location: manage_buildings.php?deleted=1");
    exit();
}

if (isset($_POST['update_building'])) {
    $id = $_POST['edit_id'];
    $name = $_POST['edit_name'];
    $flats = $_POST['edit_flats'];
    $floors = $_POST['edit_floors'];
    $address = $_POST['edit_address'];

    $stmt = $conn->prepare("UPDATE building SET building_name=?, total_flats=?, total_floors=?, address=? WHERE building_id=?");
    $stmt->bind_param("siisi", $name, $flats, $floors, $address, $id);
    $stmt->execute();
    $stmt->close();

    header("Location: manage_buildings.php?updated=1");
    exit();
}

$result = $conn->query("SELECT * FROM building ORDER BY building_id DESC");
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Manage Buildings | Aravind Sarovar</title>
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
.btn-gold:active {
  transform: scale(0.98);
  box-shadow: 0 0 10px gold;
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
a {
  text-decoration: none;
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
  <h2>🏢 Manage Buildings</h2>

  <?php if (isset($_GET['success'])): ?>
    <div class="alert text-center">✅ Building added successfully!</div>
  <?php elseif (isset($_GET['deleted'])): ?>
    <div class="alert text-center">🗑 Building deleted successfully!</div>
  <?php elseif (isset($_GET['updated'])): ?>
    <div class="alert text-center">✏️ Building updated successfully!</div>
  <?php endif; ?>

  <form method="POST" class="row g-3 mb-5">
    <div class="col-md-3">
      <input type="text" name="building_name" class="form-control" placeholder="Building Name" required>
    </div>
    <div class="col-md-2">
      <input type="number" name="total_flats" class="form-control" placeholder="Total Flats" required>
    </div>
    <div class="col-md-2">
      <input type="number" name="total_floors" class="form-control" placeholder="Total Floors" required>
    </div>
    <div class="col-md-3">
      <input type="text" name="address" class="form-control" placeholder="Address">
    </div>
    <div class="col-md-2">
      <button type="submit" name="add_building" class="btn btn-gold w-100">Add Building</button>
    </div>
  </form>

  <table class="table table-hover text-center align-middle">
    <thead>
      <tr>
        <th>ID</th>
        <th>Building Name</th>
        <th>Total Flats</th>
        <th>Floors</th>
        <th>Address</th>
        <th>Actions</th>
      </tr>
    </thead>
    <tbody>
      <?php while($row = $result->fetch_assoc()): ?>
      <tr>
        <td><?= $row['building_id'] ?></td>
        <td><?= htmlspecialchars($row['building_name']) ?></td>
        <td><?= $row['total_flats'] ?></td>
        <td><?= $row['total_floors'] ?></td>
        <td><?= htmlspecialchars($row['address']) ?></td>
        <td>
          <button class="btn btn-sm btn-gold"
            data-bs-toggle="modal"
            data-bs-target="#editModal"
            data-id="<?= $row['building_id'] ?>"
            data-name="<?= htmlspecialchars($row['building_name']) ?>"
            data-flats="<?= $row['total_flats'] ?>"
            data-floors="<?= $row['total_floors'] ?>"
            data-address="<?= htmlspecialchars($row['address']) ?>">
            ✏️ Edit
          </button>
          <a href="?delete=<?= $row['building_id'] ?>" class="btn btn-danger btn-sm" onclick="return confirm('Delete this building?')">🗑 Delete</a>
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
        <h5 class="modal-title" style="color: gold;">Edit Building</h5>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
      </div>
      <form method="POST">
        <div class="modal-body">
          <input type="hidden" name="edit_id" id="edit_id">
          <div class="mb-3">
            <label class="form-label">Building Name</label>
            <input type="text" class="form-control" name="edit_name" id="edit_name" required>
          </div>
          <div class="mb-3">
            <label class="form-label">Total Flats</label>
            <input type="number" class="form-control" name="edit_flats" id="edit_flats" required>
          </div>
          <div class="mb-3">
            <label class="form-label">Total Floors</label>
            <input type="number" class="form-control" name="edit_floors" id="edit_floors" required>
          </div>
          <div class="mb-3">
            <label class="form-label">Address</label>
            <input type="text" class="form-control" name="edit_address" id="edit_address">
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
          <button type="submit" name="update_building" class="btn btn-gold">Save Changes</button>
        </div>
      </form>
    </div>
  </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script>
const editModal = document.getElementById('editModal');
editModal.addEventListener('show.bs.modal', event => {
  const button = event.relatedTarget;
  document.getElementById('edit_id').value = button.getAttribute('data-id');
  document.getElementById('edit_name').value = button.getAttribute('data-name');
  document.getElementById('edit_flats').value = button.getAttribute('data-flats');
  document.getElementById('edit_floors').value = button.getAttribute('data-floors');
  document.getElementById('edit_address').value = button.getAttribute('data-address');
});
</script>
</body>
</html>