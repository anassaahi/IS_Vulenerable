<?php
// leaderboard.php
require 'config.php';
session_start();

// 1) Enforce login
if (!isset($_SESSION['user_id'])) {
    header('Location: index.php');
    exit();
}

// 2) Fetch aggregated scores & completed‐task counts
$sql = "
    SELECT
      u.username,
      (
        COALESCE(p.xss_totalmark, 0)
      + COALESCE(p.csrf_totalmark, 0)
      + COALESCE(p.fileupload_totalmark, 0)
      + COALESCE(p.openredirect_totalmark, 0)
      + COALESCE(p.ssrf_totalmark, 0)
      ) AS total_score,
      (
        COALESCE(p.xss_complete, 0)
      + COALESCE(p.csrf_complete, 0)
      + COALESCE(p.fileupload_complete, 0)
      + COALESCE(p.openredirect_complete, 0)
      + COALESCE(p.ssrf_complete, 0)
      ) AS tasks_completed
    FROM users AS u
    LEFT JOIN user_task_progress AS p
      ON u.user_id = p.user_id
    ORDER BY total_score DESC, u.username ASC
";
$stmt = $pdo->query($sql);
$rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Leaderboard</title>
  <!-- same fonts & icons -->
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
      --dark-bg: #0f172a;
      --card-bg: #1e293b;
    }
    * { box-sizing: border-box; margin: 0; padding: 0; font-family: 'Poppins', sans-serif; }
    body {
      background: linear-gradient(135deg, var(--dark-bg), #1a2a42);
      color: var(--light);
      display: flex;
      flex-direction: column;
      align-items: center;
      min-height: 100vh;
      padding: 30px 15px;
    }
    .intro {
      text-align: center;
      max-width: 800px;
      margin-bottom: 40px;
      animation: fadeInDown 0.8s ease;
    }
    .intro h1 {
      font-size: 3rem;
      font-weight: 700;
      background: linear-gradient(90deg, var(--accent), var(--primary));
      -webkit-background-clip: text;
      color: transparent;
      margin-bottom: 10px;
    }
    .intro p {
      font-size: 1.1rem;
      color: rgba(255,255,255,0.75);
    }
    .playground {
      background: var(--card-bg);
      padding: 2.5rem;
      border-radius: 16px;
      box-shadow: 0 15px 30px rgba(0,0,0,0.3);
      width: 100%;
      max-width: 900px;
      margin-bottom: 30px;
      animation: fadeInUp 0.8s ease;
    }
    table {
      width: 100%;
      border-collapse: collapse;
      color: var(--light);
    }
    th, td {
      padding: 12px 15px;
      border-bottom: 1px solid rgba(255,255,255,0.1);
      text-align: center;
    }
    th {
      background: var(--secondary);
      font-weight: 600;
    }
    tr:nth-child(even) {
      background: rgba(255,255,255,0.05);
    }
    .navigation {
      display: flex;
      justify-content: center;
      gap: 20px;
      margin-top: 20px;
      width: 100%;
      max-width: 800px;
    }
    .nav-btn {
      padding: 0.8rem 1.5rem;
      background: #334155;
      border-radius: 10px;
      cursor: pointer;
      transition: all 0.3s ease;
      font-weight: 500;
      border: 1px solid var(--accent);
      color: white;
      display: flex;
      align-items: center;
      gap: 8px;
      text-decoration: none;
    }
    .nav-btn:hover {
      background: var(--accent);
    }
    @keyframes fadeInUp {
      from { opacity: 0; transform: translateY(20px); }
      to { opacity: 1; transform: translateY(0); }
    }
    @keyframes fadeInDown {
      from { opacity: 0; transform: translateY(-20px); }
      to { opacity: 1; transform: translateY(0); }
    }
  </style>
</head>
<body>

  <div class="intro">
    <h1>Challenge Leaderboard</h1>
    <p>See how you stack up against your peers!</p>
  </div>

  <div class="playground">
    <table>
      <thead>
        <tr>
          <th>Rank</th>
          <th>Username</th>
          <th>Total Score</th>
          <th>Tasks Completed</th>
        </tr>
      </thead>
      <tbody>
        <?php
          $rank = 1;
          foreach ($rows as $row):
        ?>
        <tr>
          <td><?= $rank ?></td>
          <td><?= htmlspecialchars($row['username'], ENT_QUOTES, 'UTF-8') ?></td>
          <td><?= (int)$row['total_score'] ?></td>
          <td><?= (int)$row['tasks_completed'] ?>/5</td>
        </tr>
        <?php
          $rank++;
          endforeach;
        ?>
      </tbody>
    </table>
  </div>

  <div class="navigation">
    <a class="nav-btn" href="dashboard.php">
      <i class="fas fa-home"></i> Dashboard
    </a>
    <a class="nav-btn" href="logout.php">
      <i class="fas fa-sign-out-alt"></i> Logout
    </a>
  </div>

</body>
</html>
