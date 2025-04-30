<?php
include('db_connect.php');
session_start();

// Redirect if not logged in or not admin
if (!isset($_SESSION['user_id']) || $_SESSION['is_admin'] != 1) {
    header("Location: index.php");
    exit();
}

$message = "";
$group_id = isset($_GET['group_id']) ? intval($_GET['group_id']) : 0;

if ($group_id > 0) {
    // Check if group has members
    $stmt = $conn->prepare("SELECT COUNT(*) FROM group_members WHERE group_id = ?");
    $stmt->bind_param("i", $group_id);
    $stmt->execute();
    $stmt->bind_result($member_count);
    $stmt->fetch();
    $stmt->close();

    if ($member_count > 0) {
        $message = "This group has members. Please remove all members before deleting the group.";
    } else {
        // Delete the group
        $stmt = $conn->prepare("DELETE FROM groups WHERE id = ?");
        $stmt->bind_param("i", $group_id);
        if ($stmt->execute()) {
            $message = "✅ Group removed successfully.";
        } else {
            $message = "❌ Error removing group: " . $stmt->error;
        }
        $stmt->close();
    }
} else {
    $message = "⚠️ Invalid group ID.";
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Remove Group</title>
    <link rel="stylesheet" href="remove_group_styles.css">
</head>
<body>
    <div class="remove-group-container">
        <div class="remove-group-box">
            <h1>Group Removal</h1>
            <p class="message"><?php echo htmlspecialchars($message); ?></p>
            <a href="admin.php" class="back-btn">← Back to Admin</a>
        </div>
    </div>
</body>
</html>
