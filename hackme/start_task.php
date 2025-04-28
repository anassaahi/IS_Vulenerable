<?php
require 'config.php';
session_start();

// Redirect to login if not authenticated
if (!isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit();
}

// Get user progress
try {
    $stmt = $pdo->prepare("SELECT * FROM user_task_progress WHERE user_id = ?");
    $stmt->execute([$_SESSION['user_id']]);
    $progress = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($progress) {
        $tasks = ['xss', 'csrf', 'fileupload', 'sqlinjection', 'openredirect', 'ssrf'];

        foreach ($tasks as $task) {
            if (!$progress[$task . '_complete']) {
                // If the task is NOT complete, start it
                header("Location: {$task}.php");
                exit();
            }
        }

        // If all tasks are complete
        echo "<script>alert('Congratulations! You have completed all tasks.'); window.location.href = 'dashboard.php';</script>";
        exit();
    } else {
        // No progress record found
        echo "<script>alert('No progress found. Please contact admin.'); window.location.href = 'dashboard.php';</script>";
        exit();
    }

} catch (PDOException $e) {
    error_log("Database error: " . $e->getMessage());
    echo "<script>alert('Something went wrong. Try again later.'); window.location.href = 'dashboard.php';</script>";
    exit();
}
?>
