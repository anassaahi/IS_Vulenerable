<?php
// login.php
require 'config.php';
session_start();

$error = null;

try {
    // Verify database connection
    if (!$pdo) {
        throw new Exception("Database connection failed");
    }

    $login_username = trim($_POST['username'] ?? '');
    $login_password = trim($_POST['password'] ?? '');

    // Validate input
    if (empty($login_username) || empty($login_password)) {
        throw new Exception("Username and password are required");
    }

    // Check user credentials
    $stmt = $pdo->prepare("SELECT user_id, username, password FROM users WHERE username = ?");
    if (!$stmt->execute([$login_username])) {
        throw new Exception("Database query failed");
    }
    
    $user = $stmt->fetch();

    if ($user && $user['password'] === $login_password) {
        // Login successful
        $_SESSION['user_id'] = $user['user_id'];
        $_SESSION['username'] = $user['username'];
        
        header("Location: dashboard.php");
        exit();
    } else {
        throw new Exception("Invalid username or password");
    }

} catch (Exception $e) {
    $error = $e->getMessage();
    $_SESSION['login_error'] = $error;
    
    // Preserve form input
    $_SESSION['form_input'] = [
        'username' => $_POST['username'] ?? '',
    ];
    
    header("Location: index.php?action=login");
    exit();
}
?>