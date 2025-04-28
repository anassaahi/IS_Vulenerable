<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>SQL Injection Playground</title>
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

    * {
      box-sizing: border-box;
      margin: 0;
      padding: 0;
      font-family: 'Poppins', sans-serif;
    }

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
      background: linear-gradient(90deg, var(--accent), var(--danger));
      -webkit-background-clip: text;
      color: transparent;
      margin-bottom: 20px;
    }

    .intro p {
      font-size: 1.1rem;
      color: rgba(255, 255, 255, 0.75);
      line-height: 1.6;
    }

    .playground {
      background: var(--card-bg);
      padding: 2.5rem;
      border-radius: 16px;
      box-shadow: 0 15px 30px rgba(0, 0, 0, 0.3);
      width: 100%;
      max-width: 450px;
      margin-bottom: 30px;
      animation: fadeInUp 0.8s ease;
    }

    .playground h2 {
      text-align: center;
      margin-bottom: 20px;
      font-size: 1.8rem;
    }

    .input-group {
      position: relative;
      margin-bottom: 1.5rem;
    }

    .input-group i {
      position: absolute;
      left: 15px;
      top: 50%;
      transform: translateY(-50%);
      color: var(--gray);
    }

    input {
      width: 100%;
      padding: 0.9rem 1rem 0.9rem 40px;
      border: none;
      border-radius: 10px;
      background: #334155;
      color: white;
      font-size: 0.95rem;
      border: 1px solid transparent;
    }

    input:focus {
      outline: none;
      border-color: var(--accent);
      box-shadow: 0 0 0 3px rgba(72, 149, 239, 0.2);
    }

    button {
      width: 100%;
      padding: 1rem;
      border: none;
      border-radius: 10px;
      background: linear-gradient(135deg, var(--accent), var(--primary));
      color: white;
      font-weight: 600;
      font-size: 1rem;
      cursor: pointer;
      margin-top: 10px;
      transition: all 0.3s ease;
    }

    button:hover {
      transform: translateY(-2px);
    }

    .actions {
      display: flex;
      gap: 20px;
      margin-top: 20px;
      justify-content: center;
    }

    .action-btn {
      padding: 0.7rem 1.2rem;
      background: #334155;
      border-radius: 10px;
      cursor: pointer;
      transition: all 0.3s ease;
      font-weight: 500;
      border: 1px solid var(--accent);
    }

    .action-btn:hover {
      background: var(--accent);
      color: white;
    }

    .navigation {
      display: flex;
      gap: 20px;
      margin-top: 30px;
    }

    .nav-btn {
      padding: 0.8rem 1.5rem;
      background: var(--primary);
      border-radius: 10px;
      cursor: pointer;
      transition: all 0.3s ease;
      font-weight: 500;
      text-decoration: none;
      color: white;
      display: inline-block;
    }

    .nav-btn:hover {
      background: var(--secondary);
      transform: translateY(-2px);
    }

    /* Modal */
    .modal {
      display: none;
      position: fixed;
      z-index: 10;
      left: 0;
      top: 0;
      width: 100%;
      height: 100%;
      background: rgba(0,0,0,0.6);
      justify-content: center;
      align-items: center;
    }

    .modal-content {
      background: var(--card-bg);
      padding: 2rem;
      border-radius: 12px;
      width: 90%;
      max-width: 400px;
      text-align: center;
      position: relative;
    }

    .close {
      position: absolute;
      top: 15px;
      right: 20px;
      font-size: 1.5rem;
      cursor: pointer;
      color: var(--gray);
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
    <h1>SQL Injection Playground</h1>
    <p>Welcome! Your mission is to exploit SQL Injection vulnerabilities and bypass the login system. SQL Injection allows attackers to interfere with the queries that an application makes to its database. Try common payloads to login without valid credentials!</p>
  </div>

  <div class="playground">
    <h2>Login Form</h2>
    <form id="loginForm">
      <div class="input-group">
        <i class="fas fa-user"></i>
        <input type="text" id="username" placeholder="Username" required />
      </div>
      <div class="input-group">
        <i class="fas fa-lock"></i>
        <input type="password" id="password" placeholder="Password" required />
      </div>
      <button type="submit">Login</button>
    </form>

    <div class="actions">
      <div class="action-btn" id="hintBtn">Hint</div>
      <div class="action-btn" id="solutionBtn">Solution</div>
    </div>
  </div>

  <div class="navigation">
    <a class="nav-btn" href="dashboard.php">
      <i class="fas fa-home"></i> Dashboard
    </a>
    <a class="nav-btn" href="logout.php">
      <i class="fas fa-sign-out-alt"></i> Logout
    </a>
  </div>

  <!-- Modal -->
  <div class="modal" id="modal">
    <div class="modal-content">
      <span class="close" id="closeModal">&times;</span>
      <h3 id="modalTitle"></h3>
      <p id="modalText"></p>
    </div>
  </div>

  <script>
    const hintBtn = document.getElementById('hintBtn');
    const solutionBtn = document.getElementById('solutionBtn');
    const modal = document.getElementById('modal');
    const modalTitle = document.getElementById('modalTitle');
    const modalText = document.getElementById('modalText');
    const closeModal = document.getElementById('closeModal');
    const prevBtn = document.getElementById('prevBtn');
    const nextBtn = document.getElementById('nextBtn');
    const loginForm = document.getElementById('loginForm');
    const passwordInput = document.getElementById('password');
    
    // Track challenge completion
    let challengeCompleted = localStorage.getItem('challenge_completed') === 'true';
    let solutionAttempted = false;

    hintBtn.addEventListener('click', () => {
      modalTitle.textContent = "Hint 🧩";
      modalText.textContent = "Try using ' OR '1'='1 in the username or password fields.";
      modal.style.display = 'flex';
      localStorage.setItem('hint_seen', 'true');
    });

    solutionBtn.addEventListener('click', () => {
      modalTitle.textContent = "Solution 💡";
      modalText.textContent = "Username: anything \nPassword: ' OR '1'='1 \nThis will always return true in SQL query.";
      modal.style.display = 'flex';
      solutionAttempted = true;
      localStorage.setItem('solution_seen', 'true');
    });

    closeModal.addEventListener('click', () => {
      modal.style.display = 'none';
    });

    window.onclick = function(event) {
      if (event.target == modal) {
        modal.style.display = 'none';
      }
    }

    loginForm.addEventListener('submit', (e) => {
      e.preventDefault();
      const username = document.getElementById('username').value;
      const password = document.getElementById('password').value;
      
      // Check if the solution is correct
      if (password.includes("' OR '1'='1") || password.includes("' OR 1=1 --")) {
        if (!challengeCompleted) {
          // First time solving
          checkPoints();
          challengeCompleted = true;
          localStorage.setItem('challenge_completed', 'true');
        } else {
          // Already completed before
          modalTitle.textContent = "Already Solved ✅";
          modalText.textContent = "You've already completed this challenge!";
          modal.style.display = 'flex';
        }
      } else {
        // Incorrect solution
        modalTitle.textContent = "Try Again ❌";
        modalText.textContent = "That didn't work. Try another SQL injection payload.";
        modal.style.display = 'flex';
      }
    });

    function checkPoints() {
      const hintSeen = localStorage.getItem('hint_seen') === 'true';
      const solutionSeen = localStorage.getItem('solution_seen') === 'true';

      modalTitle.textContent = "Congratulations! 🎉";

      if (solutionSeen) {
        modalText.textContent = "You completed the challenge after seeing the solution. (5 points)";
      } else if (hintSeen) {
        modalText.textContent = "You completed the challenge with a hint! (8 points)";
      } else {
        modalText.textContent = "Amazing! You solved it without any help! (10 points)";
      }

      modal.style.display = 'flex';
    }

    // Navigation buttons (placeholder functionality)
    prevBtn.addEventListener('click', (e) => {
      e.preventDefault();
      modalTitle.textContent = "Navigation";
      modalText.textContent = "This would take you to the previous challenge.";
      modal.style.display = 'flex';
    });

    nextBtn.addEventListener('click', (e) => {
      e.preventDefault();
      if (challengeCompleted) {
        modalTitle.textContent = "Navigation";
        modalText.textContent = "This would take you to the next challenge.";
        modal.style.display = 'flex';
      } else {
        modalTitle.textContent = "Complete This First";
        modalText.textContent = "You need to complete this challenge before moving to the next one.";
        modal.style.display = 'flex';
      }
    });

    // Auto-focus password field for better UX
    passwordInput.focus();
  </script>
</body>
</html>