<?php
/**
 * Idiom Collector - A secure PHP application for collecting and displaying idioms
 * Security features: CSRF protection, XSS prevention, input validation, rate limiting
 */

// Start session for CSRF protection
session_start();

// Configuration
define('DATA_FILE', __DIR__ . '/idioms_data.json');
define('MAX_IDIOM_LENGTH', 500);
define('MAX_NAME_LENGTH', 100);
define('RATE_LIMIT_SECONDS', 5); // Minimum seconds between submissions

// Initialize CSRF token
if (!isset($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

// Initialize data file if it doesn't exist
if (!file_exists(DATA_FILE)) {
    file_put_contents(DATA_FILE, json_encode([]));
    chmod(DATA_FILE, 0600); // Read/write for owner only
}

$error = '';
$success = '';

// Handle AJAX requests for likes and flags
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    header('Content-Type: application/json');
    
    // Verify CSRF token
    if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
        echo json_encode(['success' => false, 'error' => 'Invalid token']);
        exit;
    }
    
    $action = $_POST['action'];
    $index = isset($_POST['index']) ? intval($_POST['index']) : -1;
    
    // Load data
    $data = json_decode(file_get_contents(DATA_FILE), true) ?: [];
    
    if ($index >= 0 && $index < count($data)) {
        if ($action === 'like') {
            // Initialize likes if not present
            if (!isset($data[$index]['likes'])) {
                $data[$index]['likes'] = 0;
            }
            $data[$index]['likes']++;
            
            // Save and return new count
            file_put_contents(DATA_FILE, json_encode($data, JSON_PRETTY_PRINT));
            echo json_encode(['success' => true, 'likes' => $data[$index]['likes']]);
            exit;
        } elseif ($action === 'flag') {
            // Toggle flag status
            $data[$index]['flagged'] = !empty($data[$index]['flagged']) ? false : true;
            
            // Save and return new status
            file_put_contents(DATA_FILE, json_encode($data, JSON_PRETTY_PRINT));
            echo json_encode(['success' => true, 'flagged' => $data[$index]['flagged']]);
            exit;
        }
    }
    
    echo json_encode(['success' => false, 'error' => 'Invalid request']);
    exit;
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Verify CSRF token
    if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
        $error = 'Security validation failed. Please try again.';
    } else {
        // Rate limiting check
        $lastSubmission = $_SESSION['last_submission'] ?? 0;
        if (time() - $lastSubmission < RATE_LIMIT_SECONDS) {
            $error = 'Please wait a few seconds before submitting again.';
        } else {
            // Validate and sanitize input
            $idiom = trim($_POST['idiom'] ?? '');
            $name = trim($_POST['name'] ?? '');
            
            if (empty($idiom)) {
                $error = 'Please enter an idiom.';
            } elseif (strlen($idiom) > MAX_IDIOM_LENGTH) {
                $error = 'Idiom is too long (max ' . MAX_IDIOM_LENGTH . ' characters).';
            } elseif (strlen($name) > MAX_NAME_LENGTH) {
                $error = 'Name is too long (max ' . MAX_NAME_LENGTH . ' characters).';
            } else {
                // Set default name if empty
                if (empty($name)) {
                    $name = 'Anonymous';
                }
                
                // Load existing data
                $data = json_decode(file_get_contents(DATA_FILE), true) ?: [];
                
                // Add new idiom
                $data[] = [
                    'idiom' => $idiom,
                    'name' => $name,
                    'timestamp' => time(),
                    'ip_hash' => hash('sha256', $_SERVER['REMOTE_ADDR'] ?? 'unknown') // Privacy-preserving IP storage
                ];
                
                // Save data
                if (file_put_contents(DATA_FILE, json_encode($data, JSON_PRETTY_PRINT))) {
                    $success = 'Your idiom has been added successfully!';
                    $_SESSION['last_submission'] = time();
                    
                    // Regenerate CSRF token after successful submission
                    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
                } else {
                    $error = 'Failed to save idiom. Please try again.';
                }
            }
        }
    }
}

// Load idioms for display
$idioms = json_decode(file_get_contents(DATA_FILE), true) ?: [];

// Filter out flagged idioms
$idioms = array_filter($idioms, function($idiom) {
    return empty($idiom['flagged']);
});

shuffle($idioms); // Randomize the order

// Helper function to safely output text
function safe_output($text) {
    return htmlspecialchars($text, ENT_QUOTES, 'UTF-8');
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Idiom Collector</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" integrity="sha512-iecdLmaskl7CVkqkXNQ/ZH/XLlvWZOJyj7Yy7tcenmpD1ypASozpmT/E0iPtmFIB46ZmdtAc9eNBvH0H/ZpiBw==" crossorigin="anonymous" referrerpolicy="no-referrer" />
    <style>
        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }
        
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Oxygen, Ubuntu, Cantarell, sans-serif;
            line-height: 1.6;
            color: #333;
            background: #ffffff;
            min-height: 100vh;
            padding: 20px;
        }
        
        .container {
            max-width: 800px;
            margin: 0 auto;
        }
        
        .card {
            background: white;
            border-radius: 12px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.2);
            padding: 30px;
            margin-bottom: 30px;
        }
        
        h1 {
            color: #0046ad;
            margin-bottom: 10px;
            font-size: clamp(1.5rem, 5vw, 2.5rem);
        }
        
        .subtitle {
            color: #666;
            margin-bottom: 25px;
            font-size: 0.95rem;
        }
        
        .form-group {
            margin-bottom: 20px;
        }
        
        label {
            display: block;
            margin-bottom: 8px;
            font-weight: 600;
            color: #444;
        }
        
        input[type="text"],
        textarea {
            width: 100%;
            padding: 12px;
            border: 2px solid #e0e0e0;
            border-radius: 8px;
            font-size: 1rem;
            font-family: inherit;
            transition: border-color 0.3s;
        }
        
        input[type="text"]:focus,
        textarea:focus {
            outline: none;
            border-color: #0046ad;
        }
        
        textarea {
            min-height: 100px;
            resize: vertical;
        }
        
        .char-count {
            text-align: right;
            font-size: 0.85rem;
            color: #999;
            margin-top: 5px;
        }
        
        button {
            background: #0046ad;
            color: white;
            border: none;
            padding: 14px 30px;
            font-size: 1rem;
            font-weight: 600;
            border-radius: 8px;
            cursor: pointer;
            transition: transform 0.2s, box-shadow 0.2s;
            width: 100%;
        }
        
        button:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(0, 70, 173, 0.4);
        }
        
        button:active {
            transform: translateY(0);
        }
        
        button:focus {
            outline: 3px solid #0046ad;
            outline-offset: 2px;
        }
        
        .alert {
            padding: 15px;
            border-radius: 8px;
            margin-bottom: 20px;
            font-weight: 500;
        }
        
        .alert-success {
            background: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }
        
        .alert-error {
            background: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }
        
        h2 {
            color: #0046ad;
            margin-bottom: 20px;
            font-size: clamp(1.3rem, 4vw, 2rem);
        }
        
        .idiom-list {
            display: grid;
            gap: 15px;
        }
        
        .idiom-item {
            background: #f8f9fa;
            padding: 20px;
            border-radius: 8px;
            border-left: 4px solid #0046ad;
            transition: transform 0.2s, box-shadow 0.2s;
        }
        
        .idiom-item:hover {
            transform: translateX(5px);
            box-shadow: 0 3px 10px rgba(0, 0, 0, 0.1);
        }
        
        .idiom-text {
            font-size: 1.1rem;
            color: #333;
            margin-bottom: 10px;
            font-style: italic;
            line-height: 1.5;
        }
        
        .idiom-meta {
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
            gap: 10px;
            font-size: 0.9rem;
            color: #666;
        }
        
        .idiom-actions {
            display: flex;
            gap: 15px;
            align-items: center;
        }
        
        .action-btn {
            background: none;
            border: none;
            cursor: pointer;
            padding: 5px 10px;
            font-size: 1.2rem;
            transition: all 0.2s;
            display: flex;
            align-items: center;
            gap: 5px;
            color: #666;
            width: auto;
        }
        
        .action-btn:hover {
            transform: scale(1.1);
            box-shadow: none;
        }
        
        .action-btn:active {
            transform: scale(0.95);
        }
        
        .action-btn.liked {
            color: #0046ad;
        }
        
        .action-btn.flagged {
            color: #d32f2f;
        }
        
        .like-count {
            font-size: 0.9rem;
            font-weight: 600;
            color: #0046ad;
        }
        
        .idiom-author {
            font-weight: 600;
            color: #0046ad;
        }
        
        .idiom-date {
            color: #999;
        }
        
        .empty-state {
            text-align: center;
            padding: 40px 20px;
            color: #666;
        }
        
        .empty-state-icon {
            font-size: 3rem;
            margin-bottom: 15px;
            opacity: 0.5;
        }
        
        @media (max-width: 600px) {
            .card {
                padding: 20px;
            }
            
            body {
                padding: 10px;
            }
            
            .idiom-meta {
                flex-direction: column;
                align-items: flex-start;
            }
        }
        
        /* Accessibility improvements */
        .sr-only {
            position: absolute;
            width: 1px;
            height: 1px;
            padding: 0;
            margin: -1px;
            overflow: hidden;
            clip: rect(0, 0, 0, 0);
            white-space: nowrap;
            border-width: 0;
        }
        
        /* Collapsible form styles */
        .form-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            cursor: pointer;
            user-select: none;
        }
        
        .toggle-btn {
            background: none;
            border: 2px solid #0046ad;
            color: #0046ad;
            width: auto;
            padding: 8px 16px;
            font-size: 0.9rem;
            margin-left: 15px;
            transition: all 0.3s;
        }
        
        .toggle-btn:hover {
            background: #0046ad;
            color: white;
            transform: translateY(0);
        }
        
        .form-content {
            max-height: 1000px;
            overflow: hidden;
            transition: max-height 0.4s ease, opacity 0.3s ease, margin-top 0.3s ease;
            opacity: 1;
            margin-top: 25px;
        }
        
        .form-content.collapsed {
            max-height: 0;
            opacity: 0;
            margin-top: 0;
        }
        
        .toggle-icon {
            display: inline-block;
            transition: transform 0.3s;
        }
        
        .toggle-icon.collapsed {
            transform: rotate(180deg);
        }
    </style>
</head>
<body>
    <div class="container">
        <!-- Submission Form -->
        <div class="card">
            <div class="form-header" onclick="toggleForm()">
                <div>
                    <h1>Idiom Collector</h1>
                    <p class="subtitle">Share your favorite idioms and sayings with the world!</p>
                </div>
                <button type="button" class="toggle-btn" id="toggleBtn" aria-label="Toggle form visibility">
                    <span class="toggle-icon" id="toggleIcon">▼</span>
                </button>
            </div>
            
            <div class="form-content" id="formContent">
                <?php if ($error): ?>
                    <div class="alert alert-error" role="alert" aria-live="polite">
                        <?php echo safe_output($error); ?>
                    </div>
                <?php endif; ?>
                
                <?php if ($success): ?>
                    <div class="alert alert-success" role="alert" aria-live="polite">
                        <?php echo safe_output($success); ?>
                    </div>
                <?php endif; ?>
            
            <form method="POST" action="" novalidate>
                <input type="hidden" name="csrf_token" value="<?php echo safe_output($_SESSION['csrf_token']); ?>">
                
                <div class="form-group">
                    <label for="idiom">
                        Write your idiom here
                        <span class="sr-only">(required, maximum <?php echo MAX_IDIOM_LENGTH; ?> characters)</span>
                    </label>
                    <textarea 
                        id="idiom" 
                        name="idiom" 
                        required
                        maxlength="<?php echo MAX_IDIOM_LENGTH; ?>"
                        aria-describedby="idiom-help"
                        placeholder="e.g., A penny saved is a penny earned"><?php echo isset($_POST['idiom']) && $error ? safe_output($_POST['idiom']) : ''; ?></textarea>
                    <div id="idiom-help" class="char-count">
                        Maximum <?php echo MAX_IDIOM_LENGTH; ?> characters
                    </div>
                </div>
                
                <div class="form-group">
                    <label for="name">
                        Name
                        <span class="sr-only">(optional, will default to "Anonymous" if left blank)</span>
                    </label>
                    <input 
                        type="text" 
                        id="name" 
                        name="name" 
                        maxlength="<?php echo MAX_NAME_LENGTH; ?>"
                        aria-describedby="name-help"
                        placeholder="Leave blank for 'Anonymous'"
                        value="<?php echo isset($_POST['name']) && $error ? safe_output($_POST['name']) : ''; ?>">
                    <div id="name-help" class="char-count">
                        Optional - defaults to "Anonymous"
                    </div>
                </div>
                
                <button type="submit">Submit Idiom</button>
            </form>
            </div>
        </div>
        
        <!-- Idioms Display -->
        <div class="card">
            <h2>Collected Idioms (<?php echo count($idioms); ?>)</h2>
            
            <?php if (empty($idioms)): ?>
                <div class="empty-state">
                    <div class="empty-state-icon" aria-hidden="true">💭</div>
                    <p>No idioms yet. Be the first to share one!</p>
                </div>
            <?php else: ?>
                <div class="idiom-list" role="list">
                    <?php foreach ($idioms as $idx => $item): ?>
                        <?php 
                            // Find original index in the data file
                            $allData = json_decode(file_get_contents(DATA_FILE), true) ?: [];
                            $originalIndex = array_search($item, $allData);
                            $likes = isset($item['likes']) ? intval($item['likes']) : 0;
                            $flagged = !empty($item['flagged']);
                        ?>
                        <article class="idiom-item" role="listitem" data-index="<?php echo $originalIndex; ?>">
                            <div class="idiom-text">
                                "<?php echo safe_output($item['idiom']); ?>"
                            </div>
                            <div class="idiom-meta">
                                <div>
                                    <span class="idiom-author">
                                        — <?php echo safe_output($item['name']); ?>
                                    </span>
                                    <br>
                                    <time class="idiom-date" datetime="<?php echo date('c', $item['timestamp']); ?>">
                                        <?php echo date('M j, Y', $item['timestamp']); ?>
                                    </time>
                                </div>
                                <div class="idiom-actions">
                                    <button 
                                        class="action-btn like-btn <?php echo $likes > 0 ? 'liked' : ''; ?>" 
                                        onclick="handleLike(<?php echo $originalIndex; ?>, this)"
                                        aria-label="Like this idiom"
                                        title="Like">
                                        <i class="fas fa-thumbs-up"></i> <span class="like-count"><?php echo $likes > 0 ? $likes : ''; ?></span>
                                    </button>
                                    <button 
                                        class="action-btn flag-btn <?php echo $flagged ? 'flagged' : ''; ?>" 
                                        onclick="handleFlag(<?php echo $originalIndex; ?>, this)"
                                        aria-label="Flag this idiom"
                                        title="Flag as inappropriate">
                                        <i class="fas fa-flag"></i>
                                    </button>
                                </div>
                            </div>
                        </article>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
    </div>
    
    <script>
        // Character counter for textarea
        const idiomTextarea = document.getElementById('idiom');
        const charCountDiv = idiomTextarea.nextElementSibling;
        
        idiomTextarea.addEventListener('input', function() {
            const remaining = <?php echo MAX_IDIOM_LENGTH; ?> - this.value.length;
            charCountDiv.textContent = remaining + ' characters remaining';
            
            if (remaining < 50) {
                charCountDiv.style.color = '#d32f2f';
            } else {
                charCountDiv.style.color = '#999';
            }
        });
        
        // Collapsible form functionality
        function toggleForm() {
            const formContent = document.getElementById('formContent');
            const toggleIcon = document.getElementById('toggleIcon');
            
            formContent.classList.toggle('collapsed');
            toggleIcon.classList.toggle('collapsed');
        }
        
        // Auto-collapse on successful submission
        <?php if ($success): ?>
        window.addEventListener('DOMContentLoaded', function() {
            const formContent = document.getElementById('formContent');
            const toggleIcon = document.getElementById('toggleIcon');
            
            // Collapse after a short delay so user can see success message
            setTimeout(function() {
                formContent.classList.add('collapsed');
                toggleIcon.classList.add('collapsed');
            }, 2000);
        });
        <?php endif; ?>
        
        // Handle like button
        function handleLike(index, button) {
            const csrfToken = '<?php echo $_SESSION['csrf_token']; ?>';
            
            fetch('', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: `action=like&index=${index}&csrf_token=${csrfToken}`
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    button.classList.add('liked');
                    const countSpan = button.querySelector('.like-count');
                    countSpan.textContent = data.likes;
                }
            })
            .catch(error => console.error('Error:', error));
        }
        
        // Handle flag button
        function handleFlag(index, button) {
            const csrfToken = '<?php echo $_SESSION['csrf_token']; ?>';
            
            if (!button.classList.contains('flagged')) {
                if (!confirm('Flag this idiom as inappropriate? This will hide it from the list.')) {
                    return;
                }
            }
            
            fetch('', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: `action=flag&index=${index}&csrf_token=${csrfToken}`
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    if (data.flagged) {
                        // Find the idiom item and remove it from the DOM with animation
                        const idiomItem = button.closest('.idiom-item');
                        idiomItem.style.transition = 'opacity 0.3s, transform 0.3s';
                        idiomItem.style.opacity = '0';
                        idiomItem.style.transform = 'translateX(-20px)';
                        
                        setTimeout(() => {
                            idiomItem.remove();
                            
                            // Check if there are any idioms left
                            const idiomList = document.querySelector('.idiom-list');
                            if (idiomList && idiomList.children.length === 0) {
                                // Show empty state
                                idiomList.innerHTML = `
                                    <div class="empty-state">
                                        <div class="empty-state-icon" aria-hidden="true">💭</div>
                                        <p>No idioms yet. Be the first to share one!</p>
                                    </div>
                                `;
                            }
                        }, 300);
                    } else {
                        button.classList.remove('flagged');
                        button.setAttribute('title', 'Flag as inappropriate');
                    }
                }
            })
            .catch(error => console.error('Error:', error));
        }
    </script>
</body>
</html>
