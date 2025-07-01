<?php
session_start();
include 'db.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = $_POST['username'];
    $password = hash('sha256', $_POST['password']);

    $sql = "SELECT * FROM admin WHERE username='$username' AND password='$password'";
    $result = $conn->query($sql);

    if ($result->num_rows == 1) {
        $_SESSION['admin'] = $username;
        header("Location: dashboard.php");
    } else {
        $error = "Invalid credentials";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Admin Login</title>
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css"/>
  <link rel="stylesheet" href="login.css"> <!-- 🔗 External CSS -->
</head>
<body>

  <div class="login-wrapper">
    <!-- Login Section -->
    <div class="login-card">
      <h4 class="card-title mb-4">Admin Login</h4>
      <?php if (isset($error)) echo "<div class='alert alert-danger'>$error</div>"; ?>
      <form method="POST">
        <div class="mb-3">
          <label>Username</label>
          <input type="text" name="username" required class="form-control">
        </div>
        <div class="mb-3">
          <label>Password</label>
          <input type="password" name="password" required class="form-control">
        </div>
        <button class="btn btn-success w-100">Login</button>
      </form>
    </div>

    <!-- Logo Section -->
    <div class="logo-section">
      <img src="logodenr.png" alt="DENR Logo">
    </div>
  </div>

</body>
</html>
