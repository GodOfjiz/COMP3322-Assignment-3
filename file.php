<?php
require_once 'login.php';

$conn = db_connect();

// Check session for streaming requests
if (!check_session()) {
    handle_unauthorized();
}

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