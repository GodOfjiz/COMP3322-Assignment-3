<?php
require_once 'login.php';

$conn = db_connect();

// Handle login form submission
if (isset($_POST['login'])) {
    if (authenticate($_POST['username'], $_POST['password'])) {
        $_SESSION['username'] = $_POST['username'];
        $_SESSION['login_time'] = time(); // Set login_time here
        header('Location: index.php');
        exit;
    }
}
// Check session and display appropriate content
if (!check_session()) {
    $error_message = '';
    if (isset($_SESSION['error'])) {
        $error_message = $_SESSION['error'];
        unset($_SESSION['error']);
    }
    display_login_form($error_message);
    exit;
}

// Display music content
display_secured_content(isSet($_GET['search']) ? $_GET['search'] : null);


// Function for login error message
function display_login_form($msg = '') {
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>2425B-ASS3</title>
    <link rel="stylesheet" href="look.css">
</head>


<body class="login-page">
    <header>
        <h1>3322 Royalty Free Music</h1>
        <p>Source: <a href="https://www.chosic.com/free-music/all/" target="_blank">https://www.chosic.com/free-music/all/</a></p>
    </header>
    
    <main>
        <form id="loginForm" action="index.php" method="post">
            <h2>Log in</h2>
            <?php if (!empty($msg)): ?>
                <div class="error-message <?php echo $error_class; ?>">
                    <?php echo ($msg); ?>
                </div>
            <?php endif; ?>
            
            <div class="form-group">
                <label for="username">Username:</label>
                <input type="text" id="username" name="username" required value="<?php echo isset($_POST['username']) ? htmlspecialchars($_POST['username']) : ''; ?>">
            </div>
            
            <div class="form-group">
                <label for="password">Password:</label>
                <input type="password" id="password" name="password" required>
            </div>
            
            <button type="submit" name="login">Log in</button>
        </form>
    </main>
    
    <script src="handle.js"></script>
</body>
</html>
<?php
}

function display_secured_content($search_term = null) {
    global $conn;
    $heading = "Top 8 Popular Music";
    $subheading = "";
    if (isset($search_term) && !empty($search_term)) {
        $heading = "Music in genre: ".htmlspecialchars($search_term);
    }
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>3322 Royalty Free Music</title>
    <link rel="stylesheet" href="look.css">
</head>
<body class="music-page">
    <header>
        <h1>3322 Royalty Free Music</h1>
        <p>Source: <a href="https://www.chosic.com/free-music/all/" target="_blank">https://www.chosic.com/free-music/all/</a></p>
    </header>
    
    <main>
    <div class="search-container">
    <form action="index.php" method="get" id="searchForm">
        <h1>Search: 
            <input type="text" name="search" id="searchInput" placeholder="Search for genre">
        </h1>
        <div class="genre-buttons">
            <button type="button" data-genre="Cinematic">Cinematic</button>
            <button type="button" data-genre="Games">Games</button>
            <button type="button" data-genre="Romantic">Romantic</button>
            <button type="button" data-genre="Study">Study</button>
            <button type="button" data-genre="">Popular</button>
        </div>
    </form>
    </div>
        
    <div class="music-container">
    <h2><?php echo $heading; ?></h2>
    <?php if (!empty($subheading)): ?>
        <p class="search-description"><?php echo $subheading; ?></p>
    <?php endif; ?>
    
    <div class="music-list">
            <?php
            // Query to get music based on search or popular
            $search_term = isset($_GET['search']) ? trim($_GET['search']) : '';
            if (isset($search_term)) {
                $query = "SELECT * FROM Music WHERE Tags LIKE '%".mysqli_real_escape_string($conn, $search_term)."%' ORDER BY Pcount DESC LIMIT 8";
                $heading = "Music in genre: ".$search_term;
            } else {
                $query = "SELECT * FROM Music ORDER BY Pcount DESC LIMIT 8";
                $heading = "Top 8 Popular Music";
            }
            
            $result = mysqli_query($conn, $query);
            
            if ($result && mysqli_num_rows($result) > 0) {
                while ($row = mysqli_fetch_assoc($result)) {
                    echo '<div class="music-item">';
                    echo '<button class="play-btn" musid="'.$row['_id'].'"><img src="play.png"></button>';
                    echo '<div class="music-info">';
                    echo '<span class="title">' .$row['Title']. '</span>';
                    echo '<span class="artist">'.$row['Artist'].'</span>';
                    echo '</div>';
                    echo '<span class="duration">'.$row['Length'].'</span>';
                    echo '<img src="CC4.png" alt="License" class="license-icon">';
                    echo '<img src="count.png" alt="Counter" class="song-counter">';
                    echo '<span class="play-count">'.$row['Pcount'].'</span>';
                    echo '<div class="tags">'.$row['Tags'].'</div>';
                    echo '</div>';
                }
            } else {
                echo '<p class="no-results">No music found'.(isset($search_term) ? ' under this genre ('.$search_term.')' : '').'</p>';
            }
            ?>
        </div>
        
        <audio id="audioPlayer"></audio>
    </main>
    
    <script src="handle.js"></script>
</body>
</html>
<?php
}
?>