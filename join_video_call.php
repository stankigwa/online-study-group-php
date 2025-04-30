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

// Check if the user is a member of the group
$sql_member_check = "SELECT COUNT(*) FROM group_members WHERE group_id = ? AND user_id = ?";
$stmt_check = $conn->prepare($sql_member_check);
$stmt_check->bind_param("ii", $group_id, $user_id);
$stmt_check->execute();
$stmt_check->bind_result($is_member);
$stmt_check->fetch();
$stmt_check->close();

if ($is_member == 0) {
    header("Location: dashboard.php");
    exit();
}

// Check if the call is live
$sql = "SELECT is_live FROM group_calls WHERE group_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $group_id);
$stmt->execute();
$stmt->bind_result($is_live);
$stmt->fetch();
$stmt->close();

// ✅ FIXED: Redirect BEFORE output
if ($is_live == 1) {
    header("Location: video.php?group_id=$group_id");
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Join Video Call - Group <?php echo htmlspecialchars($group_id); ?></title>
    <link rel="stylesheet" href="join_video_call_styles.css">
</head>
<body>

<div class="container">
    <h2>The video call for this group has not been started by the creator yet.</h2>
    <div class="message">
        Please wait until the group creator starts the video call.
    </div>
    <div class="back-btn">
        <a href="view_group.php?group_id=<?php echo $group_id; ?>">← Back to Group</a>
    </div>
</div>

</body>
</html>
