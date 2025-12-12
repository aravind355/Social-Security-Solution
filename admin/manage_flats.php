<?php
session_start();
include('../includes/db_connect.php');

if (!isset($_SESSION['role']) || $_SESSION['role'] != 'Admin') {
    header("Location: ../login.php");
    exit();
}

$buildings = $conn->query("SELECT * FROM building ORDER BY building_name ASC");

if (isset($_POST['add_flat'])) {
    $building_id = $_POST['building_id'];
    $flat_number = $_POST['flat_number'];
    $floor_number = $_POST['floor_number'];
    $flat_type = $_POST['flat_type'];

    $stmt = $conn->prepare("INSERT INTO flat_details (building_id, flat_number, floor_number, flat_type) VALUES (?, ?, ?, ?)");
    $stmt->bind_param("isis", $building_id, $flat_number, $floor_number, $flat_type);
    $stmt->execute();
    $stmt->close();

    header("Location: manage_flats.php?success=1");
    exit();
}

if (isset($_GET['delete'])) {
    $id = $_GET['delete'];
    $conn->query("DELETE FROM flat_details WHERE flat_id = $id");
    header("Location: manage_flats.php?deleted=1");
    exit();
}

if (isset($_POST['update_flat'])) {
    $id = $_POST['edit_id'];
    $building_id = $_POST['edit_building'];
    $flat_number = $_POST['edit_flat_number'];
    $floor_number = $_POST['edit_floor_number'];
    $flat_type = $_POST['edit_flat_type'];

    $stmt = $conn->prepare("UPDATE flat_details SET building_id=?, flat_number=?, floor_number=?, flat_type=? WHERE flat_id=?");
    $stmt->bind_param("isisi", $building_id, $flat_number, $floor_number, $flat_type, $id);
    $stmt->execute();
    $stmt->close();

    header("Location: manage_flats.php?updated=1");
    exit();
}

$result = $conn->query("
    SELECT f.*, b.building_name 
    FROM flat_details f
    JOIN building b ON f.building_id = b.building_id
    ORDER BY f.flat_id DESC
");
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Manage Flats | Aravind Sarovar</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
<style>
body {
  font-family: 'Segoe UI', sans-serif;
  background: linear-gradient(to right, #001f3f, #003366);
  color: white;
  min-height: 100vh;
}
h2 { color: gold; text-align: center; margin: 30px 0; }
.form-control, select {
  background: rgba(255, 255, 255, 0.1);
  border: 1px solid gold;
  color: gold;
  font-weight: 500;
}
.form-control::placeholder, select option {
  color: rgba(255, 215, 0, 0.7);
}
.form-control:focus, select:focus {
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
.table th { color: gold; }
.alert {
  background: rgba(255,215,0,0.1);
  border: 1px solid gold;
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
</style>
</head>
<body>

<nav class="navbar navbar-expand-lg navbar-dark">
  <div class="container-fluid">
    <a class="navbar-brand" href="dashboard.php">← Back to Dashboard</a>
  </div>
</nav>

<div class="container">
  <h2>🏠 Manage Flats</h2>

  <?php if (isset($_GET['success'])): ?>
    <div class="alert text-center">✅ Flat added successfully!</div>
  <?php elseif (isset($_GET['deleted'])): ?>
    <div class="alert text-center">🗑 Flat deleted successfully!</div>
  <?php elseif (isset($_GET['updated'])): ?>
    <div class="alert text-center">✏️ Flat updated successfully!</div>
  <?php endif; ?>

  <form method="POST" class="row g-3 mb-5">
    <div class="col-md-3">
      <select name="building_id" class="form-select" required>
        <option value="">Select Building</option>
        <?php while ($b = $buildings->fetch_assoc()): ?>
          <option value="<?= $b['building_id'] ?>"><?= htmlspecialchars($b['building_name']) ?></option>
        <?php endwhile; ?>
      </select>
    </div>
    <div class="col-md-3">
      <input type="text" name="flat_number" class="form-control" placeholder="Flat Number (e.g., A101)" required>
    </div>
    <div class="col-md-2">
      <input type="number" name="floor_number" class="form-control" placeholder="Floor No" required>
    </div>
    <div class="col-md-2">
      <input type="text" name="flat_type" class="form-control" placeholder="Flat Type (e.g., 2BHK)">
    </div>
    <div class="col-md-2">
      <button type="submit" name="add_flat" class="btn btn-gold w-100">Add Flat</button>
    </div>
  </form>

  <table class="table table-hover text-center align-middle">
    <thead>
      <tr>
        <th>ID</th>
        <th>Building</th>
        <th>Flat No.</th>
        <th>Floor</th>
        <th>Type</th>
        <th>Actions</th>
      </tr>
    </thead>
    <tbody>
      <?php while($row = $result->fetch_assoc()): ?>
      <tr>
        <td><?= $row['flat_id'] ?></td>
        <td><?= htmlspecialchars($row['building_name']) ?></td>
        <td><?= htmlspecialchars($row['flat_number']) ?></td>
        <td><?= $row['floor_number'] ?></td>
        <td><?= htmlspecialchars($row['flat_type']) ?></td>
        <td>
          <button class="btn btn-sm btn-gold"
            data-bs-toggle="modal"
            data-bs-target="#editModal"
            data-id="<?= $row['flat_id'] ?>"
            data-building="<?= $row['building_id'] ?>"
            data-flat="<?= htmlspecialchars($row['flat_number']) ?>"
            data-floor="<?= $row['floor_number'] ?>"
            data-type="<?= htmlspecialchars($row['flat_type']) ?>">
            ✏️ Edit
          </button>
          <a href="?delete=<?= $row['flat_id'] ?>" class="btn btn-danger btn-sm" onclick="return confirm('Delete this flat?')">🗑 Delete</a>
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
        <h5 class="modal-title" style="color: gold;">Edit Flat</h5>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
      </div>
      <form method="POST">
        <div class="modal-body">
          <input type="hidden" name="edit_id" id="edit_id">
          <div class="mb-3">
            <label class="form-label">Building</label>
            <select name="edit_building" id="edit_building" class="form-select" required>
              <?php
                $bList = $conn->query("SELECT * FROM building ORDER BY building_name ASC");
                while ($b = $bList->fetch_assoc()):
              ?>
                <option value="<?= $b['building_id'] ?>"><?= htmlspecialchars($b['building_name']) ?></option>
              <?php endwhile; ?>
            </select>
          </div>
          <div class="mb-3">
            <label class="form-label">Flat Number</label>
            <input type="text" class="form-control" name="edit_flat_number" id="edit_flat_number" required>
          </div>
          <div class="mb-3">
            <label class="form-label">Floor Number</label>
            <input type="number" class="form-control" name="edit_floor_number" id="edit_floor_number" required>
          </div>
          <div class="mb-3">
            <label class="form-label">Flat Type</label>
            <input type="text" class="form-control" name="edit_flat_type" id="edit_flat_type">
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
          <button type="submit" name="update_flat" class="btn btn-gold">Save Changes</button>
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
  document.getElementById('edit_building').value = button.getAttribute('data-building');
  document.getElementById('edit_flat_number').value = button.getAttribute('data-flat');
  document.getElementById('edit_floor_number').value = button.getAttribute('data-floor');
  document.getElementById('edit_flat_type').value = button.getAttribute('data-type');
});
</script>
</body>
</html>
