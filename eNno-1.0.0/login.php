<?php
session_start();

// CSRF Token
if (empty($_SESSION['csrf_token'])) {
  $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

$error = $_SESSION['error'] ?? '';
unset($_SESSION['error']);

$remember_me = $_COOKIE['remember_username'] ?? '';
?>
<!DOCTYPE html>
<html lang="id">

<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Login - EkstraKu</title>
  <link href="assets/img/smk.png" rel="icon">

  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600&display=swap" rel="stylesheet">
  <link href="../NiceAdmin/assets/vendor/bootstrap/css/bootstrap.min.css" rel="stylesheet">
  <link href="../NiceAdmin/assets/vendor/bootstrap-icons/bootstrap-icons.css" rel="stylesheet">

  <style>
    body {
      background: linear-gradient(135deg, #2980b9, #6dd5fa);
      min-height: 100vh;
      display: flex;
      align-items: center;
      justify-content: center;
      font-family: 'Poppins', sans-serif;
    }

    .login-card {
      background: rgba(255, 255, 255, 0.15);
      backdrop-filter: blur(14px);
      border-radius: 20px;
      padding: 40px;
      width: 100%;
      max-width: 420px;
      color: #fff;
      box-shadow: 0 8px 32px rgba(31, 38, 135, 0.4);
      animation: fadeIn 1s ease;
    }

    .login-card h3 {
      font-weight: 600;
      margin-bottom: 10px;
      text-align: center;
    }

    .login-card p {
      font-size: 14px;
      color: #dfe6e9;
      text-align: center;
      margin-bottom: 25px;
    }

    .form-control {
      background: rgba(255, 255, 255, 0.2);
      border: 1px solid rgba(255, 255, 255, 0.3);
      color: #fff;
    }

    .form-control::placeholder {
      color: #d0d0d0;
    }

    .input-group-text {
      background: rgba(0, 0, 0, 0.2);
      border: none;
      color: #fff;
    }

    .btn-primary {
      background: linear-gradient(to right, #2a5298, #1e3c72);
      border: none;
      font-weight: 600;
      letter-spacing: 0.5px;
      transition: 0.3s;
    }

    .btn-primary:hover {
      background: linear-gradient(to right, #1e3c72, #2a5298);
      transform: translateY(-2px);
    }

    .alert {
      background: rgba(255, 0, 0, 0.7);
      border: none;
      color: white;
    }

    .password-toggle {
      cursor: pointer;
      position: absolute;
      right: 10px;
      top: 50%;
      transform: translateY(-50%);
      color: #fff;
    }

    .password-container {
      position: relative;
    }

    @keyframes fadeIn {
      from {
        opacity: 0;
        transform: translateY(-20px);
      }

      to {
        opacity: 1;
        transform: translateY(0);
      }
    }
  </style>
</head>

<body>

  <div class="login-card">
    <div class="d-flex justify-content-center mb-3">
      <img src="assets/img/smk.png" alt="Logo" style="width: 65px;">
    </div>
    <h3>Selamat Datang</h3>
    <p>Silakan login untuk melanjutkan</p>

    <?php if (!empty($error)) : ?>
      <div class="alert alert-danger alert-dismissible fade show" role="alert">
        <?= htmlspecialchars($error, ENT_QUOTES, 'UTF-8') ?>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="alert"></button>
      </div>
    <?php endif; ?>

    <form action="login_proses.php" method="POST" id="loginForm">
      <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?>">

      <div class="mb-3">
        <label for="username" class="form-label">Username</label>
        <div class="input-group">
          <span class="input-group-text"><i class="bi bi-person"></i></span>
          <input type="text" class="form-control" id="username" name="username"
            placeholder="Masukkan username" required
            value="<?= htmlspecialchars($remember_me, ENT_QUOTES, 'UTF-8') ?>">
        </div>
      </div>

      <div class="mb-3 password-container">
        <label for="password" class="form-label">Password</label>
        <div class="input-group">
          <span class="input-group-text"><i class="bi bi-lock"></i></span>
          <input type="password" class="form-control" id="password" name="password"
            placeholder="Masukkan password" required>
          <i class="bi bi-eye-slash password-toggle" id="togglePassword"></i>
        </div>
      </div>

      <div class="mb-3 form-check">
        <input type="checkbox" class="form-check-input" id="remember" name="remember"
          <?= $remember_me ? 'checked' : '' ?>>
        <label class="form-check-label" for="remember">Ingat Saya</label>
      </div>

      <button type="submit" class="btn btn-primary w-100">Login</button>

      <div class="text-center mt-3">
        <!-- <a href="lupa_password.php" class="text-light small">Lupa Password?</a> |  -->
        <a href="daftar.php" class="text-light small">Daftar Akun</a>
      </div>
    </form>
  </div>

  <script src="../NiceAdmin/assets/vendor/bootstrap/js/bootstrap.bundle.min.js"></script>
  <script>
    // Toggle Password Visibility
    document.getElementById('togglePassword').addEventListener('click', function() {
      const passwordInput = document.getElementById('password');
      const icon = this;
      if (passwordInput.type === 'password') {
        passwordInput.type = 'text';
        icon.classList.replace('bi-eye-slash', 'bi-eye');
      } else {
        passwordInput.type = 'password';
        icon.classList.replace('bi-eye', 'bi-eye-slash');
      }
    });
  </script>
</body>
</html>