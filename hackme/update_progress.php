<?php
require 'config.php';
session_start();

if (!isset($_SESSION['user_id'])) {
    header("HTTP/1.1 401 Unauthorized");
    exit(json_encode(['error' => 'Unauthorized']));
}

$input = json_decode(file_get_contents('php://input'), true);
$task = filter_var($input['task'] ?? '', FILTER_SANITIZE_STRING);
$type = filter_var($input['type'] ?? '', FILTER_SANITIZE_STRING);

try {
    $column = $task . '_' . ($type === 'hint' ? 'hintseen' : 'solutionseen');
    $stmt = $pdo->prepare("UPDATE user_task_progress SET $column = 1 WHERE user_id = ?");
    $stmt->execute([$_SESSION['user_id']]);
    echo json_encode(['success' => true]);
} catch (PDOException $e) {
    error_log("Database error: " . $e->getMessage());
    header("HTTP/1.1 500 Internal Server Error");
    echo json_encode(['error' => 'Database error']);
}