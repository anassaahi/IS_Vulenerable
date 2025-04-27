<?php
require 'config.php';
session_start();

if (!isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit();
}

$userId = $_SESSION['user_id'];
$taskName = 'xss';

// Get current progress
try {
    $stmt = $pdo->prepare("SELECT * FROM user_task_progress WHERE user_id = ?");
    $stmt->execute([$userId]);
    $progress = $stmt->fetch(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    error_log("Database error: " . $e->getMessage());
    $progress = [];
}

// Handle AJAX requests
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['complete'])) {
    // Calculate marks
    $baseScore = 50;
    $deduction = 0;
    
    if ($progress[$taskName.'_solutionseen']) $deduction += 80;
    if ($progress[$taskName.'_hintseen']) $deduction += 30;
    
    $marks = max($baseScore - ($baseScore * $deduction / 100), 0);
    $marks = (int)round($marks);

    try {
        $stmt = $pdo->prepare("UPDATE user_task_progress 
                             SET xss_complete = 1, 
                                 xss_totalmark = ? 
                             WHERE user_id = ?");
        $stmt->execute([$marks, $userId]);
        echo json_encode(['success' => true, 'message' => "Challenge completed! Score: $marks/50"]);
        exit();
    } catch (PDOException $e) {
        error_log("Database error: " . $e->getMessage());
        echo json_encode(['error' => "Error saving progress"]);
        exit();
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>XSS Playground</title>
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
      background: linear-gradient(90deg, var(--warning), var(--danger));
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
      max-width: 800px;
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

    input, textarea {
      width: 100%;
      padding: 0.9rem 1rem 0.9rem 40px;
      border: none;
      border-radius: 10px;
      background: #334155;
      color: white;
      font-size: 0.95rem;
      border: 1px solid transparent;
    }

    textarea {
      min-height: 100px;
      resize: vertical;
    }

    input:focus, textarea:focus {
      outline: none;
      border-color: var(--accent);
      box-shadow: 0 0 0 3px rgba(72, 149, 239, 0.2);
    }

    button {
      width: 100%;
      padding: 1rem;
      border: none;
      border-radius: 10px;
      background: linear-gradient(135deg, var(--warning), var(--danger));
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
      border: 1px solid var(--warning);
    }

    .action-btn:hover {
      background: var(--warning);
      color: white;
    }

    .result-container {
      margin-top: 30px;
      padding: 20px;
      background: #334155;
      border-radius: 10px;
      border: 1px solid var(--warning);
    }

    .result-container h3 {
      margin-bottom: 15px;
      color: var(--warning);
    }

    #commentDisplay {
      min-height: 100px;
      padding: 15px;
      background: #1e293b;
      border-radius: 8px;
    }

    .navigation {
      display: flex;
      justify-content: space-between;
      width: 100%;
      max-width: 800px;
      margin-top: 20px;
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
    }

    .nav-btn:hover {
      background: var(--accent);
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
    <h1>XSS Playground</h1>
    <p>Welcome to the XSS Challenge! Cross-Site Scripting (XSS) allows attackers to inject malicious scripts into web pages viewed by other users. Try injecting scripts that execute in the context of other users' browsers. The goal is to display an alert box with the message "XSS Success!".</p>
  </div>

  <div class="playground">
    <h2>Vulnerable Comment System</h2>
    <form id="commentForm">
      <div class="input-group">
        <i class="fas fa-user"></i>
        <input type="text" id="name" placeholder="Your Name" required />
      </div>
      <div class="input-group">
        <i class="fas fa-comment"></i>
        <textarea id="comment" placeholder="Your Comment" required></textarea>
      </div>
      <button type="submit">Post Comment</button>
    </form>

    <div class="result-container">
      <h3>Comment Preview:</h3>
      <div id="commentDisplay">
        <p>Your comment will appear here...</p>
      </div>
    </div>

    <div class="actions">
      <div class="action-btn" id="hintBtn">Hint</div>
      <div class="action-btn" id="solutionBtn">Solution</div>
    </div>
  </div>

  <div class="navigation">
    <button class="nav-btn" id="prevBtn">
      <i class="fas fa-arrow-left"></i> Previous
    </button>
    <button class="nav-btn" id="nextBtn">
      Next <i class="fas fa-arrow-right"></i>
    </button>
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
  document.addEventListener('DOMContentLoaded', function() {
      const hintBtn = document.getElementById('hintBtn');
      const solutionBtn = document.getElementById('solutionBtn');
      const modal = document.getElementById('modal');
      const modalTitle = document.getElementById('modalTitle');
      const modalText = document.getElementById('modalText');
      const closeModal = document.getElementById('closeModal');
      const commentForm = document.getElementById('commentForm');
      const commentDisplay = document.getElementById('commentDisplay');
      
      let xssSuccessful = false;
      const originalAlert = window.alert;

      // Override alert to detect XSS success
      window.alert = function(message) {
          if (message.includes("XSS Success")) {
              xssSuccessful = true;
              showSuccessMessage();
          }
          return originalAlert.apply(window, arguments);
      };

      // Comment form submission
      commentForm.addEventListener('submit', function(e) {
          e.preventDefault();
          xssSuccessful = false;
          
          const name = document.getElementById('name').value;
          const comment = document.getElementById('comment').value;
          
          // Vulnerable code - directly injecting user input
          commentDisplay.innerHTML = `
              <div class="comment">
                  <strong>${name}</strong>
                  <p>${comment}</p>
              </div>
          `;
          
          // Check for delayed XSS
          setTimeout(function() {
              if (!xssSuccessful && containsXSSAttempt(comment)) {
                  checkForDelayedXSS();
              }
          }, 500);
      });

      // Hint button
      hintBtn.addEventListener('click', function() {
          updateProgress('hint')
              .then(function() {
                  showModal("Hint 🧩", "Try injecting a script tag with JavaScript code. For example: <code>&lt;script&gt;alert('XSS Success!')&lt;/script&gt;</code>");
              })
              .catch(function(error) {
                  console.error('Error:', error);
              });
      });

      // Solution button
      solutionBtn.addEventListener('click', function() {
          updateProgress('solution')
              .then(function() {
                  showModal("Solution 💡", `Enter this as your comment:<br><br>
                      <code>&lt;script&gt;alert('XSS Success!')&lt;/script&gt;</code>
                      <br><br>
                      Or this alternative:<br><br>
                      <code>&lt;img src=x onerror=alert('XSS Success!')&gt;</code>`);
              })
              .catch(function(error) {
                  console.error('Error:', error);
              });
      });

      // Close modal
      closeModal.addEventListener('click', function() {
          modal.style.display = 'none';
      });

      window.onclick = function(event) {
          if (event.target == modal) {
              modal.style.display = 'none';
          }
      };

      // Helper functions
      function containsXSSAttempt(comment) {
          return /<script|onerror|onload|javascript:/i.test(comment);
      }

      function checkForDelayedXSS() {
          const scripts = commentDisplay.getElementsByTagName('script');
          for (let script of scripts) {
              try {
                  new Function(script.text)();
              } catch (e) {
                  console.log("XSS attempt failed:", e);
              }
          }
      }

      function showSuccessMessage() {
          const form = document.createElement('form');
          form.method = 'POST';
          form.innerHTML = `
              <input type="hidden" name="complete" value="1">
              <button type="submit" style="margin-top: 20px;">Claim Your Score</button>
          `;
          
          form.addEventListener('submit', function(e) {
              e.preventDefault();
              fetch(window.location.href, {
                  method: 'POST',
                  body: new FormData(form)
              })
              .then(response => response.json())
              .then(data => {
                  if (data.success) {
                      modalText.innerHTML = data.message;
                      form.remove();
                  } else {
                      modalText.innerHTML = data.error || "Error occurred";
                  }
              })
              .catch(error => {
                  modalText.innerHTML = "Failed to save progress";
              });
          });

          modalTitle.textContent = "XSS Successful! 🎯";
          modalText.innerHTML = "Calculating your score...";
          modalText.appendChild(form);
          modal.style.display = 'flex';
      }

      function showModal(title, content) {
          modalTitle.textContent = title;
          modalText.innerHTML = content;
          modal.style.display = 'flex';
      }

      function updateProgress(type) {
          return fetch('update_progress.php', {
              method: 'POST',
              headers: {
                  'Content-Type': 'application/json',
              },
              body: JSON.stringify({
                  task: 'xss',
                  type: type
              })
          })
          .then(response => response.json());
      }
  });
  </script>

</body>
</html>