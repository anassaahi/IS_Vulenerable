<?php
require 'config.php';
session_start();

$error = null;

try {
    if(!$pdo){
        throw new Exception("Database connection failed");
    }

    $username = trim($_POST['username'] ?? '');
    $password = trim($_POST['password'] ?? '');

    // Validate input
    if (empty($username)) {
        throw new Exception("Username cannot be empty");
    }

    if (strlen($username) < 4) {
        throw new Exception("Username must be at least 4 characters");
    }

    if (empty($password)) {
        throw new Exception("Password cannot be empty");
    }

    if (strlen($password) < 4) {
        throw new Exception("Password must be at least 4 characters");
    }

    $stmt = $pdo->prepare("SELECT user_id FROM users WHERE username = ?");
    if (!$stmt->execute([$username])) {
        throw new Exception("Database query failed");
    }
    
    if ($stmt->fetch()) {
        throw new Exception("Username already exists");
    }

    $stmt = $pdo->prepare("INSERT INTO users (username, password) VALUES (?, ?)");
    if (!$stmt->execute([$username, $password])) {
        throw new Exception("Failed to create user: " . implode(", ", $stmt->errorInfo()));
    }

    if ($stmt->rowCount() === 0) {
        throw new Exception("No rows affected - user not created");
    }

    $user_id = $pdo->lastInsertId();
    
    $challengeStmt = $pdo->prepare("SELECT challenge_id FROM challenges");
    $challengeStmt->execute();
    $challenges = $challengeStmt->fetchAll(PDO::FETCH_COLUMN);
    
    $progressStmt = $pdo->prepare("
        INSERT INTO user_progress 
        (user_id, challenge_id, has_seen_hint, has_seen_solution, is_completed, score)
        VALUES (?, ?, FALSE, FALSE, FALSE, 0)
    ");
    
    foreach ($challenges as $challenge_id) {
        $progressStmt->execute([$user_id, $challenge_id]);
    }

    // Log in the new user
    $_SESSION['user_id'] = $user_id;
    $_SESSION['username'] = $username;
    
    header("Location: dashboard.php");
    exit();

} catch (Exception $e) {
    $error = $e->getMessage();
    $_SESSION['signup_error'] = $error;
    
    // Preserve form input
    $_SESSION['form_input'] = [
        'username' => $_POST['username'] ?? '',
    ];
    
    header("Location: index.php?action=signup");
    exit();
}
?>