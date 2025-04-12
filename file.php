<?php
require_once 'login.php';

// Check session for streaming requests
if (!check_session()) {
    handle_unauthorized();
}


$conn = db_connect();
$musicId = mysqli_real_escape_string($conn, $_GET['musid']);

// Get music file details
$query = "SELECT Filename FROM Music WHERE _id = '$musicId'";
$result = mysqli_query($conn, $query);

if (!$result || mysqli_num_rows($result) == 0) {
    header("Location: index.php?error=music_not_found");
    exit;
}

$music = mysqli_fetch_assoc($result);
$filePath = './Music/' . $music['Filename'];

if (!file_exists($filePath)) {
    header("Location: index.php?error=file_not_found");
    exit;
}

// Update play count
$updateQuery = "UPDATE Music SET Pcount = Pcount + 1 WHERE _id = '$musicId'";
mysqli_query($conn, $updateQuery);

header('Content-Type: audio/mpeg');
header('Content-Length: ' . filesize($filePath));
header('Cache-Control: no-cache');
readfile($filePath);

mysqli_close($conn);
exit;
?>
login.php:
<?php
// Session configuration
define("SessionTime", 4);
ini_set('session.gc_maxlifetime', SessionTime);
session_set_cookie_params(SessionTime);

// Start session
session_start();

// Database configuration
define("DB_HOST", "mydb");
define("DB_USER", "dummy");
define("DB_PASS", "c3322b");
define("DB_NAME", "db3322");

// Connect to database
function db_connect() {
    $conn = mysqli_connect(DB_HOST, DB_USER, DB_PASS, DB_NAME);
    if (!$conn) {
        header("Location: index.php?error=database_error");
        exit;
    }
    return $conn;
}

// Check if session is valid
function check_session() {
    // If the user isn't logged in, immediately return false
    if (!isset($_SESSION['username'])) {
        return false;
    }
    
    // Use a fixed login time instead of continuously updating last_activity
    if (!isset($_SESSION['login_time'])) {
        // If for some reason login_time isn't set, set it now.
        $_SESSION['login_time'] = time();
    }
    
    // Check if the absolute session lifetime has been exceeded
    if (time() - $_SESSION['login_time'] >= SessionTime) {
        // Set the error message, clear and destroy the session
        $error_message = 'Session expired!!';
        session_unset();
        session_destroy();

        // Restart session solely to pass the error message
        session_start();
        $_SESSION['error'] = $error_message;
        
        return false;
    }
    
    return true;
}

function authenticate($username, $password) {
    $conn = db_connect();
    $username = mysqli_real_escape_string($conn, $username);
    
    $query = "SELECT username, password FROM account WHERE username = '$username'";
    $result = mysqli_query($conn, $query);
    
    if (mysqli_num_rows($result) === 0) {
        $_SESSION['error'] = 'No such user!';
        return false;
    }
    
    $user = mysqli_fetch_assoc($result);
    mysqli_close($conn);
    
    if ($password !== $user['password']) {
        $_SESSION['error'] = 'Incorrect password!';
        return false;
    }
    
    return true;
}

function handle_unauthorized() {
    if (isset($_SESSION['error']) && $_SESSION['error'] === 'Session expired!!') {
        unset($_SESSION['error']);
        header('Location: index.php');
    } else {
        header("HTTP/1.1 401 Unauthorized");
        exit("Unauthorized access");
    }
    exit;
}
?>