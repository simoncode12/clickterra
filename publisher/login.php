<?php
// File: /publisher/login.php (REDESIGNED)
if (session_status() === PHP_SESSION_NONE) { session_start(); }
if (isset($_SESSION['publisher_id'])) { header('Location: dashboard.php'); exit(); }
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Publisher Portal Login</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style> body { display: flex; align-items: center; height: 100vh; background-color: #f0f2f5; } .login-card { max-width: 400px; margin: auto; border: none; box-shadow: 0 4px 25px rgba(0,0,0,0.1); } </style>
</head>
<body>
    <div class="card login-card">
        <div class="card-body p-4 p-md-5">
            <div class="text-center mb-4">
                 </div>
            <h4 class="card-title text-center mb-4 fw-bold">Publisher Login</h4>
            <?php if (isset($_SESSION['login_error'])): ?>
                <div class="alert alert-danger"><?php echo $_SESSION['login_error']; unset($_SESSION['login_error']); ?></div>
            <?php endif; ?>
            <form action="auth.php" method="POST">
                <div class="mb-3"><label for="username" class="form-label">Username</label><input type="text" class="form-control" name="username" required></div>
                <div class="mb-3"><label for="password" class="form-label">Password</label><input type="password" class="form-control" name="password" required></div>
                <div class="d-grid mt-4"><button type="submit" class="btn btn-primary btn-lg">Login</button></div>
            </form>
        </div>
    </div>
</body>
</html>