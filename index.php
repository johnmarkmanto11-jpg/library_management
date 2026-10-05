<?php
session_start();
if (isset($_SESSION['user_id'])) { header("Location: dashboard.php"); exit; }
$error = $_GET['error'] ?? '';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Library Management System</title>
    <link rel="stylesheet" href="style.css">
</head>
<body class="login-page">
<div class="login-card">
    <h1>Library Management System</h1>
    <p class="subtitle">Log in to continue</p>
    <?php if ($error): ?><div class="alert error"><?= htmlspecialchars($error) ?></div><?php endif; ?>
    <form action="login.php" method="POST">
        <label>Username</label>
        <input type="text" name="username" required autofocus>
        <label>Password</label>
        <input type="password" name="password" required>
        <button type="submit" class="btn primary full">Log in</button>
    </form>
    <div class="demo">
        <b>Demo accounts</b><br>
        Administrator: <code>admin / admin123</code><br>
        Student: <code>student / student123</code>
    </div>
</div>
</body>
</html>
