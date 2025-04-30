<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit();
}

include 'db_connect.php';

$user_id = $_SESSION['user_id'];

if (!isset($_GET['group_id']) || !is_numeric($_GET['group_id'])) {
    header("Location: dashboard.php");
    exit();
}

$group_id = intval($_GET['group_id']);

// Check if current user is the creator of the group
$sql = "SELECT user_id FROM groups WHERE id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $group_id);
$stmt->execute();
$stmt->bind_result($creator_id);
$stmt->fetch();
$stmt->close();

if ($creator_id != $user_id) {
    header("Location: view_group.php?group_id=$group_id");
    exit();
}

// Set group call as live
$sql_live = "INSERT INTO group_calls (group_id, is_live) 
             VALUES (?, 1) 
             ON DUPLICATE KEY UPDATE is_live = 1";
$stmt_live = $conn->prepare($sql_live);
$stmt_live->bind_param("i", $group_id);
$stmt_live->execute();
$stmt_live->close();

// Redirect to the video call room
header("Location: video.php?group_id=$group_id");
exit();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Start Video Call - Group <?php echo htmlspecialchars($group_id); ?></title>
    <link rel="stylesheet" href="start_video_call_styles.css">
</head>
<body>
    <div class="container">
        <h1>Start Video Call for Group</h1>

        <div class="video-call-container">
            <a href="video_call.php?group_id=<?php echo $group_id; ?>" class="start-call-btn">Start the Video Call</a>
        </div>

        <div class="instructions">
            <h3>Instructions:</h3>
            <ul>
                <li>Only the group creator can start the video call.</li>
                <li>Once the video call is started, members can join.</li>
                <li>Make sure your camera and microphone are working before starting the call.</li>
            </ul>
        </div>

        <div class="back-btn">
            <a href="view_group.php?group_id=<?php echo $group_id; ?>" class="back-to-dashboard-btn">← Back to Group</a>
        </div>
    </div>
</body>
</html>
