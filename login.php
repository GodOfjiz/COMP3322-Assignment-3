<?php
// Session configuration
define("SessionTime", 4);
ini_set('session.gc_maxlifetime', SessionTime);
session_set_cookie_params(0);

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
    if (!isset($_SESSION['username'])) {
        return false;
    }
    
    if (time() - $_SESSION['login_time'] >= SessionTime) {
        $_SESSION['error'] = 'Session expired';
        // Unset only the authentication variables
        unset($_SESSION['username']);
        unset($_SESSION['login_time']);
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
    if (isset($_SESSION['timeout_occurred'])) {
        unset($_SESSION['timeout_occurred']);
        header('Location: index.php');
        exit;
    } else {
        header("HTTP/1.1 401 Unauthorized");
        exit("Unauthorized access");
    }
}
?>