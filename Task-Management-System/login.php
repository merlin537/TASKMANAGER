<?php
session_start();
include "DB_connection.php"; // DB connection file

$message = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    // REGISTER
    if (isset($_POST['register'])) {
        $full_name = trim($_POST['full_name']);
        $username  = trim($_POST['username']);
        $password  = trim($_POST['password']);
        $role      = trim($_POST['role']);

        if (!empty($full_name) && !empty($username) && !empty($password) && !empty($role)) {
            $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

            $sql = "INSERT INTO users (full_name, username, password, role) VALUES (?, ?, ?, ?)";
            $stmt = $conn->prepare($sql);

            try {
                $stmt->execute([$full_name, $username, $hashedPassword, $role]);
                $message = "✅ Registration successful. Please log in.";
            } catch (PDOException $e) {
                $message = "❌ Error: Username already exists.";
            }
        } else {
            $message = "⚠️ All fields are required for registration.";
        }
    }

    // LOGIN
    if (isset($_POST['login'])) {
        $username = trim($_POST['username']);
        $password = trim($_POST['password']);

        $sql = "SELECT * FROM users WHERE username = ?";
        $stmt = $conn->prepare($sql);
        $stmt->execute([$username]);

        if ($stmt->rowCount() === 1) {
            $user = $stmt->fetch(PDO::FETCH_ASSOC);

            if (password_verify($password, $user['password'])) {
                $_SESSION['id']       = $user['id'];
                $_SESSION['username'] = $user['username'];
                $_SESSION['role']     = $user['role'];

                // redirect based on role
               header("Location: index.php");
              exit();

                exit();
            } else {
                $message = "❌ Invalid password.";
            }
        } else {
            $message = "⚠️ User not found.";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Login | Task Management System</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <style>
    body {
      background: linear-gradient(to bottom, #00c6ff, #7b2ff7);
      height: 100vh;
      display: flex;
      align-items: center;
      justify-content: center;
      font-family: Arial, sans-serif;
    }
    .auth-box {
      background: #fff;
      border-radius: 15px;
      padding: 40px;
      width: 400px;
      box-shadow: 0px 0px 20px rgba(0,0,0,0.2);
      text-align: center;
    }
    .auth-box img {
      width: 80px;
      margin-bottom: 20px;
    }
    .form-control {
      border-radius: 25px;
      margin-bottom: 15px;
      padding: 12px 20px;
    }
    .btn-custom {
      width: 100%;
      border-radius: 25px;
      padding: 10px;
      background: linear-gradient(to right, #00c6ff, #7b2ff7);
      border: none;
      color: #fff;
      font-weight: bold;
    }
    .toggle-link {
      display: block;
      margin-top: 15px;
      color: #7b2ff7;
      cursor: pointer;
      font-weight: bold;
    }
  </style>
</head>
<body>

<div class="auth-box">
    <img src="https://cdn-icons-png.flaticon.com/512/847/847969.png" alt="User Icon">
    <h3 id="form-title">Sign In</h3>

    <?php if (!empty($message)) { ?>
        <div class="alert alert-info"><?= htmlspecialchars($message) ?></div>
    <?php } ?>

    <!-- LOGIN FORM -->
    <form method="POST" id="login-form">
        <input type="text" name="username" class="form-control" placeholder="Username" required>
        <input type="password" name="password" class="form-control" placeholder="Password" required>
        <button type="submit" name="login" class="btn-custom">Sign In</button>
    </form>

    <!-- REGISTER FORM (hidden initially) -->
    <form method="POST" id="register-form" style="display:none;">
        <input type="text" name="full_name" class="form-control" placeholder="Full Name" required>
        <input type="text" name="username" class="form-control" placeholder="Username" required>
        <input type="password" name="password" class="form-control" placeholder="Password" required>
        <select name="role" class="form-control" required>
            <option value="employee">Employee</option>
            <option value="admin">Admin</option>
        </select>
        <button type="submit" name="register" class="btn-custom">Create Account</button>
    </form>

    <span class="toggle-link" id="toggle-link">Not a member? Create account</span>
</div>

<script>
  const toggleLink = document.getElementById("toggle-link");
  const loginForm = document.getElementById("login-form");
  const registerForm = document.getElementById("register-form");
  const formTitle = document.getElementById("form-title");

  toggleLink.addEventListener("click", function() {
    if (loginForm.style.display === "none") {
      loginForm.style.display = "block";
      registerForm.style.display = "none";
      formTitle.textContent = "Sign In";
      toggleLink.textContent = "Not a member? Create account";
    } else {
      loginForm.style.display = "none";
      registerForm.style.display = "block";
      formTitle.textContent = "Register";
      toggleLink.textContent = "Already have an account? Sign in";
    }
  });
</script>

</body>
</html>
