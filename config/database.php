<?php
// config/database.php
// SAFE Railway-compatible DB config with startSession()
// No output, no warnings, suitable for production/dev.

// -------------------------------
// 0. Ensure no accidental output here (no BOM, no whitespace before <?php)
// -------------------------------

// -------------------------------
// 1. Read envs safely
// -------------------------------
$publicUrl = getenv('MYSQL_PUBLIC_URL');
$envDbName = getenv('MYSQLDATABASE');
$envHost   = getenv('MYSQLHOST');
$envPort   = getenv('MYSQLPORT');
$envUser   = getenv('MYSQLUSER');
$envPass   = getenv('MYSQLPASSWORD');

$defined = false;

if ($publicUrl && is_string($publicUrl)) {
    $parts = @parse_url($publicUrl);
    if ($parts && is_array($parts)) {
        $host = $parts['host'] ?? null;
        $port = isset($parts['port']) ? (int)$parts['port'] : null;
        $user = $parts['user'] ?? null;
        $pass = $parts['pass'] ?? null;
        $path = $parts['path'] ?? null;
        $dbname = $path !== null ? ltrim($path, "/") : null;

        if ($host && $user && $dbname) {
            define('DB_HOST', $host);
            define('DB_PORT', $port ?: 3306);
            define('DB_USER', $user);
            define('DB_PASS', $pass ?? '');
            define('DB_NAME', $dbname);
            $defined = true;
        }
    }
}

// fallback to Railway internal envs
if (!$defined && $envDbName) {
    define('DB_HOST', $envHost ?: 'localhost');
    define('DB_PORT', $envPort ? (int)$envPort : 3306);
    define('DB_USER', $envUser ?: 'root');
    define('DB_PASS', $envPass ?: '');
    define('DB_NAME', $envDbName);
    $defined = true;
}

// local fallback
if (!$defined) {
    define('DB_HOST', 'localhost');
    define('DB_PORT', 3306);
    define('DB_USER', 'root');
    define('DB_PASS', '');
    define('DB_NAME', 'earabic');
}


// -------------------------------
// 2. Session helper (does not auto-start here)
// -------------------------------
function startSession() {
    if (session_status() === PHP_SESSION_NONE) {
        // set strict mode early — safe to call before session_start()
        @ini_set('session.use_strict_mode', 1);
        session_start();
    }
}


// -------------------------------
// 3. DB connection helper
// -------------------------------
mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

function getConnection() {
    $host = DB_HOST;
    $user = DB_USER;
    $pass = DB_PASS;
    $name = DB_NAME;
    $port = defined('DB_PORT') ? (int) DB_PORT : 3306;

    try {
        $conn = new mysqli($host, $user, $pass, $name, $port);
        $conn->set_charset('utf8mb4');
        return $conn;
    } catch (Throwable $e) {
        // log internal error; do not echo to browser (prevents header problems)
        error_log('DB CONNECTION ERROR: ' . $e->getMessage());
        // fail gracefully
        http_response_code(500);
        exit('Database connection failed.');
    }
}


// -------------------------------
// 4. Auth helpers
// -------------------------------
function isLoggedIn() {
    // ensure session available
    if (session_status() === PHP_SESSION_NONE) {
        // do not attempt ini_set here (may already be sent), just start
        startSession();
    }
    return isset($_SESSION['user_id']);
}

function getCurrentUser() {
    if (session_status() === PHP_SESSION_NONE) {
        startSession();
    }

    if (empty($_SESSION['user_id'])) {
        return null;
    }

    try {
        $conn = getConnection();
        $stmt = $conn->prepare('SELECT * FROM users WHERE id = ?');
        $stmt->bind_param('i', $_SESSION['user_id']);
        $stmt->execute();
        $result = $stmt->get_result();
        $user = $result->fetch_assoc();
        $stmt->close();
        $conn->close();
        return $user;
    } catch (Throwable $e) {
        error_log('getCurrentUser error: ' . $e->getMessage());
        return null;
    }
}


// -------------------------------
// 5. Redirect helper
// -------------------------------
function redirect($page) {
    header('Location: ' . $page);
    exit();
}
