<?php
require 'config.php';
session_start();

// Initialize variables
$error = isset($_SESSION['error']) ? $_SESSION['error'] : null;
unset($_SESSION['error']);

// Determine if we're showing login or signup form
$isLogin = !(isset($_GET['action']) && $_GET['action'] === 'signup');

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['login'])) {
        // Handle login
        $username = trim($_POST['username'] ?? '');
        $password = trim($_POST['password'] ?? '');
        
        try {
            // Validate input
            if (empty($username) || empty($password)) {
                throw new Exception("Username and password are required");
            }

            // Check credentials
            $stmt = $pdo->prepare("SELECT user_id, username, password FROM users WHERE username = ?");
            $stmt->execute([$username]);
            $user = $stmt->fetch();

            if ($user && $user['password'] === $password) {
                // Login successful
                $_SESSION['user_id'] = $user['user_id'];
                $_SESSION['username'] = $user['username'];
                header("Location: dashboard.php");
                exit();
            } else {
                throw new Exception("Invalid username or password");
            }
        } catch (Exception $e) {
            $_SESSION['error'] = $e->getMessage();
            header("Location: index.php?action=login");
            exit();
        }
        
    } elseif (isset($_POST['signup'])) {
        // Handle signup
        $username = trim($_POST['username'] ?? '');
        $password = trim($_POST['password'] ?? '');
        
        try {
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

            if (strlen($password) < 8) {
                throw new Exception("Password must be at least 4 characters");
            }

            // Check if user exists
            $stmt = $pdo->prepare("SELECT user_id FROM users WHERE username = ?");
            $stmt->execute([$username]);
            if ($stmt->fetch()) {
                throw new Exception("Username already exists");
            }

            // Create user with plain text password
            $stmt = $pdo->prepare("INSERT INTO users (username, password) VALUES (?, ?)");
            $stmt->execute([$username, $password]);

            // Verify insertion
            if ($stmt->rowCount() === 0) {
                throw new Exception("Failed to create user");
            }

            // Get new user ID and log in
            $user_id = $pdo->lastInsertId();
            $_SESSION['user_id'] = $user_id;
            $_SESSION['username'] = $username;
            
            header("Location: dashboard.php");
            exit();

        } catch (Exception $e) {
            $_SESSION['error'] = $e->getMessage();
            $_SESSION['form_input'] = ['username' => $username];
            header("Location: index.php?action=signup");
            exit();
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Vulnerable - <?php echo $isLogin ? 'Login' : 'Sign Up'; ?></title>
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
      margin: 0;
      background: linear-gradient(135deg, var(--dark-bg), #1a2a42);
      display: flex;
      flex-direction: column;
      justify-content: center;
      align-items: center;
      min-height: 100vh;
      color: var(--light);
      padding: 20px;
    }

    .welcome-header {
      text-align: center;
      margin-bottom: 40px;
      animation: fadeInDown 0.8s ease;
    }

    .welcome-title {
      font-size: 3.5rem;
      font-weight: 700;
      background: linear-gradient(90deg, var(--accent), var(--danger));
      -webkit-background-clip: text;
      background-clip: text;
      color: transparent;
      margin-bottom: 10px;
      line-height: 1.2;
    }

    .welcome-subtitle {
      font-size: 1.2rem;
      color: rgba(255, 255, 255, 0.7);
      font-weight: 300;
    }

    .auth-container {
      background: var(--card-bg);
      padding: 2.5rem;
      border-radius: 16px;
      box-shadow: 0 15px 30px rgba(0, 0, 0, 0.3);
      width: 100%;
      max-width: 400px;
      animation: fadeInUp 0.8s ease;
      position: relative;
      overflow: hidden;
      z-index: 1;
    }

    .auth-container::before {
      content: '';
      position: absolute;
      top: -50%;
      left: -50%;
      width: 200%;
      height: 200%;
      background: linear-gradient(
        to bottom right,
        rgba(255, 255, 255, 0.03),
        rgba(255, 255, 255, 0.01)
      );
      transform: rotate(30deg);
      z-index: -1;
    }

    .auth-container h2 {
      margin-bottom: 1.8rem;
      text-align: center;
      font-size: 1.8rem;
      font-weight: 600;
      color: white;
      position: relative;
      display: inline-block;
      width: 100%;
    }

    .auth-container h2::after {
      content: '';
      position: absolute;
      bottom: -10px;
      left: 50%;
      transform: translateX(-50%);
      width: 60px;
      height: 3px;
      background: linear-gradient(90deg, var(--accent), var(--danger));
      border-radius: 3px;
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
      margin-bottom: 0.25rem;
      border: none;
      border-radius: 10px;
      background: #334155;
      color: white;
      font-size: 0.95rem;
      transition: all 0.3s ease;
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
      margin: 1.5rem 0;
      transition: all 0.3s ease;
      box-shadow: 0 5px 15px rgba(67, 97, 238, 0.3);
      position: relative;
      overflow: hidden;
    }

    button:hover {
      transform: translateY(-2px);
      box-shadow: 0 8px 20px rgba(67, 97, 238, 0.4);
    }

    button:active {
      transform: translateY(0);
    }

    button::after {
      content: '';
      position: absolute;
      top: -50%;
      left: -50%;
      width: 200%;
      height: 200%;
      background: linear-gradient(
        to bottom right,
        rgba(255, 255, 255, 0.2),
        rgba(255, 255, 255, 0.1)
      );
      transform: rotate(30deg);
      transition: all 0.3s ease;
    }

    button:hover::after {
      left: 100%;
    }

    .error-message {
      color: var(--danger);
      margin-bottom: 1rem;
      text-align: center;
      animation: fadeIn 0.3s ease;
    }

    .toggle {
      text-align: center;
      cursor: pointer;
      color: #94a3b8;
      font-size: 0.95rem;
      transition: color 0.3s ease;
    }

    .toggle:hover {
      color: var(--accent);
    }

    .toggle span {
      color: var(--accent);
      font-weight: 500;
    }

    @keyframes fadeIn {
      from { opacity: 0; }
      to { opacity: 1; }
    }

    @keyframes fadeInUp {
      from {
        opacity: 0;
        transform: translateY(20px);
      }
      to {
        opacity: 1;
        transform: translateY(0);
      }
    }

    @keyframes fadeInDown {
      from {
        opacity: 0;
        transform: translateY(-20px);
      }
      to {
        opacity: 1;
        transform: translateY(0);
      }
    }

    @media (max-width: 480px) {
      .welcome-title {
        font-size: 2.5rem;
      }
      
      .auth-container {
        padding: 1.8rem;
      }
    }
  </style>
</head>
<body>
  <div class="welcome-header">
    <h1 class="welcome-title">Welcome to Hack Me!</h1>
    <p class="welcome-subtitle">Secure your skills with our interactive platform</p>
  </div>

  <div class="auth-container">
    <h2 id="formTitle"><?php echo $isLogin ? 'Login' : 'Sign Up'; ?></h2>
    <form id="authForm" method="POST">
      <div class="input-group">
        <i class="fas fa-user"></i>
        <input type="text" id="username" name="username" placeholder="Username" required 
               value="<?php echo isset($_SESSION['form_input']['username']) ? htmlspecialchars($_SESSION['form_input']['username']) : ''; ?>" />
      </div>
      <div class="input-group">
        <i class="fas fa-lock"></i>
        <input type="password" id="password" name="password" placeholder="Password" required />
      </div>
      <?php if ($error): ?>
        <div class="error-message"><?php echo htmlspecialchars($error); ?></div>
      <?php endif; ?>
      <button type="submit" name="<?php echo $isLogin ? 'login' : 'signup'; ?>">
        <i class="fas <?php echo $isLogin ? 'fa-sign-in-alt' : 'fa-user-plus'; ?>" style="margin-right: 8px;"></i>
        <span id="submitText"><?php echo $isLogin ? 'Login' : 'Sign Up'; ?></span>
      </button>
    </form>
    <div class="toggle" id="toggleForm">
      <?php echo $isLogin ? "Don't have an account? <span>Sign up</span>" : "Already have an account? <span>Login</span>"; ?>
    </div>
  </div>

  <script>
    let isLogin = <?php echo $isLogin ? 'true' : 'false'; ?>;

    const form = document.getElementById('authForm');
    const toggle = document.getElementById('toggleForm');
    const formTitle = document.getElementById('formTitle');
    const submitText = document.getElementById('submitText');
    const button = form.querySelector('button');

    // Update form based on current mode
    function updateForm() {
      formTitle.textContent = isLogin ? 'Login' : 'Sign Up';
      submitText.textContent = isLogin ? 'Login' : 'Sign Up';
      toggle.innerHTML = isLogin 
        ? "Don't have an account? <span>Sign up</span>" 
        : "Already have an account? <span>Login</span>";
      
      // Update the button name attribute for PHP processing
      button.name = isLogin ? 'login' : 'signup';
      button.querySelector('i').className = isLogin ? 'fas fa-sign-in-alt' : 'fas fa-user-plus';
    }

    // Toggle between login/signup
    toggle.addEventListener('click', (e) => {
      e.preventDefault();
      isLogin = !isLogin;
      
      // Change URL parameter without reload for better UX
      const url = new URL(window.location.href);
      url.searchParams.set('action', isLogin ? 'login' : 'signup');
      window.history.pushState({}, '', url);
      
      updateForm();
      
      // Clear any existing error messages
      const errorDiv = document.querySelector('.error-message');
      if (errorDiv) {
        errorDiv.remove();
      }
    });

    // Simple client-side validation
    form.addEventListener('submit', (e) => {
      const username = document.getElementById('username').value.trim();
      const password = document.getElementById('password').value.trim();
      
      if (!username || !password) {
        e.preventDefault();
        showError('Please fill in all fields');
        return;
      }
      
      if (!isLogin && password.length < 4) {
        e.preventDefault();
        showError('Password must be at least 4 characters');
        return;
      }
    });

    // Helper function to show errors
    function showError(message) {
      // Remove existing error if any
      const existingError = document.querySelector('.error-message');
      if (existingError) {
        existingError.remove();
      }
      
      // Create error element
      const errorDiv = document.createElement('div');
      errorDiv.className = 'error-message';
      errorDiv.textContent = message;
      
      // Insert before the button
      button.parentNode.insertBefore(errorDiv, button);
    }
  </script>
</body>
</html>
<?php
// Clear form input from session after displaying
if (isset($_SESSION['form_input'])) {
    unset($_SESSION['form_input']);
}
?>