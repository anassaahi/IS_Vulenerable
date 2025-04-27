<?php
require 'config.php';
session_start();

// Redirect to login if not authenticated
if (!isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit();
}

// Get user progress data
try {
    $stmt = $pdo->prepare("SELECT * FROM user_task_progress WHERE user_id = ?");
    $stmt->execute([$_SESSION['user_id']]);
    $progress = $stmt->fetch(PDO::FETCH_ASSOC);
    
} catch (PDOException $e) {
    // Handle database error
    error_log("Database error: " . $e->getMessage());
    $progress = [];
}

// Task configuration
$tasks = [
    'sqlinjection' => [
        'title' => 'SQL Injection',
        'icon' => 'fa-database',
        'page' => 'sqlinjection.php',
        'color' => 'sql'
    ],
    'xss' => [
        'title' => 'Cross-Site Scripting',
        'icon' => 'fa-code',
        'page' => 'xss.php',
        'color' => 'xss'
    ],
    'csrf' => [
        'title' => 'CSRF',
        'icon' => 'fa-exchange-alt',
        'page' => 'csrf.php',
        'color' => 'csrf'
    ],
    'openredirect' => [
        'title' => 'Open Redirect',
        'icon' => 'fa-external-link-alt',
        'page' => 'openredirect.php',
        'color' => 'redirect'
    ],
    'fileupload' => [
        'title' => 'File Upload',
        'icon' => 'fa-file-upload',
        'page' => 'fileupload.php',
        'color' => 'upload'
    ],
    'ssrf' => [
        'title' => 'SSRF',
        'icon' => 'fa-server',
        'page' => 'ssrf.php',
        'color' => 'ssrf'
    ]
];
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Security Challenges</title>
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
      --gray: #6c757d;
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
      justify-content: space-between;
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
    
    .challenges-grid {
      display: grid;
      grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
      gap: 25px;
      margin-bottom: 40px;
    }
    
    .challenge-card {
      background: white;
      padding: 30px;
      border-radius: 16px;
      box-shadow: 0 10px 20px rgba(0, 0, 0, 0.05);
      text-align: center;
      transition: all 0.3s ease;
      cursor: pointer;
      position: relative;
      overflow: hidden;
    }
    
    .challenge-card::before {
      content: '';
      position: absolute;
      top: 0;
      left: 0;
      width: 100%;
      height: 5px;
      background: linear-gradient(90deg, var(--primary), var(--danger));
    }
    
    .challenge-card:hover {
      transform: translateY(-5px);
      box-shadow: 0 15px 30px rgba(0, 0, 0, 0.1);
    }
    
    .challenge-icon {
      font-size: 48px;
      margin-bottom: 20px;
      transition: all 0.3s ease;
    }
    
    .challenge-card:hover .challenge-icon {
      transform: scale(1.1);
    }
    
    .challenge-title {
      font-size: 22px;
      font-weight: 600;
      margin-bottom: 10px;
    }
    
    .challenge-desc {
      font-size: 14px;
      color: #6c757d;
      margin-bottom: 20px;
    }
    
    .challenge-progress {
      height: 6px;
      background: #e9ecef;
      border-radius: 3px;
      margin-bottom: 15px;
      overflow: hidden;
    }
    
    .progress-bar {
      height: 100%;
      background: linear-gradient(90deg, var(--accent), var(--primary));
      border-radius: 3px;
      transition: width 0.6s ease;
    }
    
    .challenge-status {
      font-size: 12px;
      font-weight: 500;
      color: var(--primary);
    }
    
    /* Challenge-specific colors */
    .challenge-sql { --card-color: #4cc9f0; }
    .challenge-xss { --card-color: #f8961e; }
    .challenge-csrf { --card-color: #7209b7; }
    .challenge-redirect { --card-color: #3a86ff; }
    .challenge-upload { --card-color: #f72585; }
    .challenge-ssrf { --card-color: #4361ee; }
    
    .challenge-card::before {
      background: linear-gradient(90deg, var(--card-color), color-mix(in srgb, var(--card-color) 20%, white));
    }
    /* Animations */
    @keyframes float {
      0%, 100% { transform: translateY(0); }
      50% { transform: translateY(-10px); }
    }
    
    .challenge-card:hover {
      animation: float 3s ease-in-out infinite;
    }
    
    @keyframes pulse {
      0% { box-shadow: 0 0 0 0 rgba(67, 97, 238, 0.4); }
      70% { box-shadow: 0 0 0 10px rgba(67, 97, 238, 0); }
      100% { box-shadow: 0 0 0 0 rgba(67, 97, 238, 0); }
    }
    
    .new-challenge {
      animation: pulse 2s infinite;
    }
    
    @media (max-width: 768px) {
      .challenges-grid {
        grid-template-columns: 1fr;
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
      <i class="fas fa-shield-alt"></i>
      <h2>Security Challenges</h2>
    </div>
    
    <div class="sidebar-menu">
      <a href="dashboard.php">
        <i class="fas fa-arrow-left"></i>
        Back to Dashboard
      </a>
      <a href="leaderboard.php">
        <i class="fas fa-trophy"></i>
        Leaderboard
      </a>
      <a href="#">
        <i class="fas fa-book"></i>
        Documentation
      </a>
    </div>
  </div>
  
  <a href="logout.php" class="logout-btn">
    <i class="fas fa-sign-out-alt"></i>
    Logout
  </a>
</div>

<div class="main">
  <div class="welcome-section">
    <p class="welcome-text">Practice your skills with</p>
    <h1 class="username">Security Challenges</h1>
  </div>
  
  <div class="challenges-grid">
    <?php foreach ($tasks as $taskKey => $taskConfig): 
        $completed = $progress[$taskKey.'_complete'] ?? false;
        $marks = $progress[$taskKey.'_totalmark'] ?? 0;
    ?>
    <div class="challenge-card challenge-<?php echo $taskConfig['color']; ?>" 
         onclick="navigateTo('<?php echo $taskConfig['page']; ?>')">
      <div class="challenge-icon">
        <i class="fas <?php echo $taskConfig['icon']; ?>"></i>
      </div>
      <h3 class="challenge-title"><?php echo $taskConfig['title']; ?></h3>
      <p class="challenge-desc"><?php // Keep existing description text ?></p>
      
      <div class="challenge-status">
        <?php if($completed): ?>
          <span style="color: var(--success);">✓ Completed</span><br>
          Marks: <?php echo $marks; ?>/50
        <?php else: ?>
          <span style="color: var(--warning);">◌ Not Completed</span><br>
          Current Marks: <?php echo $marks; ?>/50
        <?php endif; ?>
      </div>
      
      <?php if($taskKey === 'xss' && !$completed): ?>
        <div class="challenge-status" style="color: var(--danger); margin-top: 10px;">
          New challenge!
        </div>
      <?php endif; ?>
    </div>
    <?php endforeach; ?>
  </div>
</div>
<script>
  function navigateTo(page) {
    window.location.href = page;
  }

  // Add animation on load
  document.addEventListener('DOMContentLoaded', () => {
    const cards = document.querySelectorAll('.challenge-card');
    cards.forEach((card, index) => {
      card.style.opacity = '0';
      card.style.transform = 'translateY(20px)';
      card.style.animation = `fadeInUp 0.5s ease-out ${index * 0.1}s forwards`;
    });
    
    // Add styles for animations
    const style = document.createElement('style');
    style.textContent = `
      @keyframes fadeInUp {
        from { opacity: 0; transform: translateY(20px); }
        to { opacity: 1; transform: translateY(0); }
      }
    `;
    document.head.appendChild(style);
  });
</script>
<!-- <script>
  // Check if user is logged in
  // const username = localStorage.getItem('username');
  // if (!username) {
  //   window.location.href = 'login.html';
  // }

  // function navigateTo(page) {
  //   window.location.href = page;
  // }

  // function logout() {
  //   localStorage.removeItem('username');
  //   window.location.href = 'login.html';
  // }

  // Add animation on load
  document.addEventListener('DOMContentLoaded', () => {
    const cards = document.querySelectorAll('.challenge-card');
    cards.forEach((card, index) => {
      card.style.opacity = '0';
      card.style.transform = 'translateY(20px)';
      card.style.animation = `fadeInUp 0.5s ease-out ${index * 0.1}s forwards`;
    });
    
    // Add styles for animations
    const style = document.createElement('style');
    style.textContent = `
      @keyframes fadeInUp {
        from { opacity: 0; transform: translateY(20px); }
        to { opacity: 1; transform: translateY(0); }
      }
    `;
    document.head.appendChild(style);
  });
</script> -->

</body>
</html>