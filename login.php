<?php
include('includes/db_connect.php');
session_start();

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = trim($_POST['username']);
    $password = $_POST['password'];

    $stmt = $conn->prepare("SELECT * FROM login_credentials WHERE username=?");
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 1) {
        $row = $result->fetch_assoc();
        if (password_verify($password, $row['password'])) {
            $_SESSION['user_id'] = $row['id'];
            $_SESSION['username'] = $row['username'];
            $_SESSION['role'] = $row['role'];

            if ($row['role'] == 'Admin') {
                header("Location: admin/dashboard.php");
            } elseif ($row['role'] == 'Supervisor') {
                header("Location: supervisor/dashboard.php");
            } else {
                header("Location: resident/dashboard.php");
            }

            exit();
        } else {
            $error = "Invalid password.";
        }
    } else {
        $error = "User not found.";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Login | Aravind Sarovar</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
<style>
  body {
    margin: 0;
    height: 100vh;
    background: url('images/login_bg.png') no-repeat center center/cover;
    display: flex;
    align-items: center;
    justify-content: center;
    backdrop-filter: blur(6px);
  }
  .login-box {
    background: rgba(255, 255, 255, 0.1);
    backdrop-filter: blur(12px);
    border: 2px solid gold;
    border-radius: 20px;
    box-shadow: 0 0 30px rgba(255, 215, 0, 0.3);
    width: 360px;
    padding: 40px;
    color: #fff;
    text-align: center;
  }
  .login-box h2 {
    font-weight: 600;
    color: gold;
    margin-bottom: 25px;
  }
  .form-control {
    background: rgba(255,255,255,0.15);
    border: 1px solid gold;
    color: #fff;
    border-radius: 10px;
  }
  .form-control:focus {
    box-shadow: 0 0 8px gold;
    background: rgba(255,255,255,0.2);
  }
  .btn-login {
    background: gold;
    color: #003366;
    font-weight: bold;
    border-radius: 10px;
    transition: all 0.3s;
  }
  .btn-login:hover {
    background: #ffcf40;
    transform: translateY(-2px);
  }
  .error-msg {
    color: #ff4444;
    margin-top: 15px;
  }
</style>
</head>
<body>
  <div class="login-box">
    <h2>Aravind Sarovar</h2>
    <form method="POST">
      <div class="mb-3">
        <input type="text" name="username" placeholder="Username" class="form-control" required>
      </div>
      <div class="mb-3">
        <input type="password" name="password" placeholder="Password" class="form-control" required>
      </div>
      <button type="submit" class="btn btn-login w-100">Login</button>
      <?php if (!empty($error)) echo "<p class='error-msg'>$error</p>"; ?>
    </form>
  </div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>