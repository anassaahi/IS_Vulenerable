<?php
require 'config.php';
session_start();

// Redirect to login if not authenticated
if (!isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit();
}

// Get current user information
$username = $_SESSION['username'];

// Get user progress data
try {
    $stmt = $pdo->prepare("SELECT * FROM user_task_progress WHERE user_id = ?");
    $stmt->execute([$_SESSION['user_id']]);
    $progress = $stmt->fetch(PDO::FETCH_ASSOC);
    
    // Calculate metrics
    $totalScore = 0;
    $completedTasks = 0;
    
    if ($progress) {
        // List of all task names
        $tasks = ['xss', 'csrf', 'fileupload', 'sqlinjection', 'openredirect', 'ssrf'];
        
        foreach ($tasks as $task) {
            // Calculate total score
            $totalScore += $progress[$task . '_totalmark'];
            
            // Count completed tasks
            if ($progress[$task . '_complete']) {
                $completedTasks++;
            }
        }
    }
    
    // Calculate remaining tasks
    $totalTasks = 6;
    $remainingTasks = $totalTasks - $completedTasks;
    
} catch (PDOException $e) {
    // Handle database error
    error_log("Database error: " . $e->getMessage());
    $totalScore = 0;
    $completedTasks = 0;
    $remainingTasks = 6;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Dashboard</title>
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
  <style>
    :root {
      --primary: #4361ee;
      --secondary: #3f37c9;
      --accent: #4895ef;
      --dark: #2b2d42;
      --light: #f8f9fa;
      --success: #4cc9f0;
      --warning: #f8961e;
      --danger: #f72585;
    }
    
    body {
      margin: 0;
      font-family: 'Poppins', sans-serif;
      display: flex;
      height: 100vh;
      background-color: #f7f9fc;
      color: var(--dark);
    }
    
    .sidebar {
      width: 280px;
      background: linear-gradient(135deg, var(--secondary), var(--primary));
      color: var(--light);
      display: flex;
      flex-direction: column;
      padding: 30px 20px;
      box-shadow: 4px 0 15px rgba(0, 0, 0, 0.1);
      z-index: 10;
      justify-content: space-between; /* Added to push logout to bottom */
    }
    
    .sidebar-header {
      display: flex;
      align-items: center;
      margin-bottom: 40px;
      padding: 0 10px;
    }
    
    .sidebar-header i {
      font-size: 24px;
      margin-right: 15px;
      color: var(--light);
    }
    
    .sidebar h2 {
      margin: 0;
      font-size: 22px;
      font-weight: 600;
    }
    
    .sidebar-menu {
      flex: 1;
    }
    
    .sidebar a {
      color: rgba(255, 255, 255, 0.9);
      text-decoration: none;
      padding: 12px 15px;
      margin: 8px 0;
      font-size: 16px;
      display: flex;
      align-items: center;
      border-radius: 8px;
      transition: all 0.3s ease;
    }
    
    .sidebar a i {
      margin-right: 12px;
      font-size: 18px;
      width: 24px;
      text-align: center;
    }
    
    .sidebar a:hover {
      background: rgba(255, 255, 255, 0.15);
      transform: translateX(5px);
    }
    
    .sidebar a.active {
      background: rgba(255, 255, 255, 0.2);
      font-weight: 500;
    }
    
    .logout-btn {
      margin-top: 20px;
      padding: 12px 15px;
      color: rgba(255, 255, 255, 0.9);
      text-decoration: none;
      font-size: 16px;
      display: flex;
      align-items: center;
      border-radius: 8px;
      transition: all 0.3s ease;
      background: rgba(255, 255, 255, 0.1);
      border: none;
      cursor: pointer;
      width: 100%;
    }
    
    .logout-btn i {
      margin-right: 12px;
      font-size: 18px;
      width: 24px;
      text-align: center;
    }
    
    .logout-btn:hover {
      background: rgba(255, 255, 255, 0.2);
      transform: translateX(5px);
    }
    
    .main {
      flex: 1;
      padding: 40px;
      overflow-y: auto;
    }
    
    .welcome-section {
      margin-bottom: 40px;
    }
    
    .welcome-text {
      font-size: 18px;
      color: #6c757d;
      margin-bottom: 5px;
    }
    
    .username {
      font-size: 36px;
      font-weight: 700;
      background: linear-gradient(90deg, var(--primary), var(--danger));
      -webkit-background-clip: text;
      background-clip: text;
      color: transparent;
      margin: 0;
      line-height: 1.2;
    }
    
    .cards {
      display: flex;
      gap: 25px;
      margin-bottom: 40px;
    }
    
    .card {
      flex: 1;
      background: white;
      padding: 30px;
      border-radius: 16px;
      box-shadow: 0 10px 20px rgba(0, 0, 0, 0.05);
      text-align: center;
      transition: transform 0.3s ease, box-shadow 0.3s ease;
    }
    
    .card:hover {
      transform: translateY(-5px);
      box-shadow: 0 15px 30px rgba(0, 0, 0, 0.1);
    }
    
    .card-value {
      font-size: 36px;
      font-weight: 700;
      margin: 10px 0;
    }
    
    .card-label {
      font-size: 16px;
      color: #6c757d;
    }
    
    .card-score {
      color: var(--primary);
    }
    
    .card-certificates {
      color: var(--warning);
    }
    
    .card-exercises {
      color: var(--danger);
    }
    
    .start-test-btn {
      margin-top: 40px;
      padding: 18px 30px;
      background: linear-gradient(135deg, var(--accent), var(--primary));
      color: white;
      font-size: 18px;
      font-weight: 600;
      border: none;
      border-radius: 12px;
      cursor: pointer;
      transition: all 0.3s ease;
      box-shadow: 0 5px 15px rgba(67, 97, 238, 0.3);
      position: relative;
      overflow: hidden;
    }
    
    .start-test-btn:hover {
      transform: translateY(-3px);
      box-shadow: 0 8px 25px rgba(67, 97, 238, 0.4);
    }
    
    .start-test-btn:active {
      transform: translateY(1px);
    }
    
    .start-test-btn::after {
      content: '';
      position: absolute;
      top: -50%;
      left: -50%;
      width: 200%;
      height: 200%;
      background: linear-gradient(
        to bottom right,
        rgba(255, 255, 255, 0.3),
        rgba(255, 255, 255, 0.1)
      );
      transform: rotate(30deg);
      transition: all 0.3s ease;
    }
    
    .start-test-btn:hover::after {
      left: 100%;
    }
    
    @media (max-width: 768px) {
      .cards {
        flex-direction: column;
      }
      
      .sidebar {
        width: 220px;
      }
    }
  </style>
</head>
<body>

<div class="sidebar">
  <div>
    <div class="sidebar-header">
      <i class="fas fa-chart-line"></i>
      <h2>Dashboard</h2>
    </div>
    
    <div class="sidebar-menu">
      <a href="#" class="active">
        <i class="fas fa-home"></i>
        Overview
      </a>
      <a href="progress.php">
        <i class="fas fa-chart-bar"></i>
        Progress
      </a>
      <a href="leaderboard.php">
        <i class="fas fa-bookmark"></i>
        Leaderboard
      </a>
      <a href="#">
        <i class="fas fa-award"></i>
        Badges
      </a>
      <a href="#">
        <i class="fas fa-cog"></i>
        Settings
      </a>
      <a href="start_task.php" class="start-test-btn">
  <i class="fas fa-play"></i>
  Start Test
</a>

    </div>
  </div>
  
  <!-- Logout Button -->
  <!-- Logout Button -->
<a href="logout.php" class="logout-btn">
    <i class="fas fa-sign-out-alt"></i>
    Logout
</a>
</div>

<div class="main">
  <div class="welcome-section">
    <p class="welcome-text">Welcome back,</p>
    <h1 class="username" id="userInfo"><?php echo htmlspecialchars($username); ?></h1>
  </div>
  
  <div class="cards">
    <div class="card">
      <div class="card-value card-score" id="solved"><?php echo $totalScore; ?></div>
      <div class="card-label">Score</div>
    </div>
    <div class="card">
      <div class="card-value card-certificates" id="certificates"><?php echo $completedTasks; ?></div>
      <div class="card-label">Tasks Completed</div>
    </div>
    <div class="card">
      <div class="card-value card-exercises" id="exercises"><?php echo $remainingTasks; ?></div>
      <div class="card-label">Exercises Left</div>
    </div>
  </div>

  <!-- <a href="start_task.php" class="start-test-btn">
  <i class="fas fa-play"></i>
  Start Test
</a> -->

</div>

<!-- <script>
  function startTest() {
    window.location.href = 'start_task.php';
  }
</script> -->

<script>
  // Check if user is logged in
  // const username = localStorage.getItem('username');
  // if (!username) {
  //   window.location.href = 'login.html';
  // } else {
  //   document.getElementById('userInfo').innerText = username;
  // }

  function startTest() {
  // Change this to use your server's URL
  window.location.href = 'xss.html';
}
  function logout() {
    // Clear user data from localStorage
    localStorage.removeItem('username');
    
    // Redirect to login page
    window.location.href = 'index.php';
  }
</script>

</body>
</html>