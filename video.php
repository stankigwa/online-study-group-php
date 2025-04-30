<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$group_id = isset($_GET['group_id']) ? $_GET['group_id'] : null;

// Assuming you have a way to fetch group info from your database
$group_name = "Sample Group"; // Replace this with a database query

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Video Call - <?php echo htmlspecialchars($group_name); ?> - Online Study Group</title>
    <link rel="stylesheet" href="video_styles.css"> <!-- Link to your specific CSS file -->
</head>
<body>
    <div class="video-container">
        <div class="video-header">
            <h1>Video Call - <?php echo htmlspecialchars($group_name); ?></h1>
        </div>

        <div class="video-buttons">
            <a href="https://meet.jit.si/<?php echo htmlspecialchars($group_name); ?>" target="_blank" class="btn join-btn">Join Video Call</a>
        </div>

        <div class="instructions">
            <p>Click on the "Join Video Call" button to enter the group video call.</p>
        </div>
    </div>
</body>
</html>
