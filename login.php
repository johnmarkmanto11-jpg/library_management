<?php
require "config.php";

$username = trim($_POST['username'] ?? '');
$password = $_POST['password'] ?? '';

$stmt = $conn->prepare("SELECT id, name, username, password, role FROM users WHERE username = ?");
$stmt->bind_param("s", $username);
$stmt->execute();
$user = $stmt->get_result()->fetch_assoc();

if ($user && password_verify($password, $user['password'])) {
    session_regenerate_id(true);
    $_SESSION['user_id']  = $user['id'];
    $_SESSION['name']     = $user['name'];
    $_SESSION['username'] = $user['username'];
    $_SESSION['role']     = $user['role'];
    header("Location: dashboard.php");
    exit;
}

header("Location: index.php?error=" . urlencode("Invalid username or password."));
exit;
