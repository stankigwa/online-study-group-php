<?php
session_start();
include('db_connect.php');

if (!isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$group_id = isset($_GET['group_id']) ? intval($_GET['group_id']) : 0;

if ($group_id > 0) {
    // Check if user is the creator of the group
    $stmt = $conn->prepare("SELECT * FROM groups WHERE id = ? AND user_id = ?");
    $stmt->bind_param("ii", $group_id, $user_id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 1) {
        // If confirmation submitted
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['confirm_delete'])) {
            // First, delete group members
            $stmt_members = $conn->prepare("DELETE FROM group_members WHERE group_id = ?");
            $stmt_members->bind_param("i", $group_id);
            $stmt_members->execute();

            // Then delete the group
            $stmt_delete = $conn->prepare("DELETE FROM groups WHERE id = ?");
            $stmt_delete->bind_param("i", $group_id);
            if ($stmt_delete->execute()) {
                header("Location: dashboard.php?message=Group+deleted+successfully");
                exit();
            } else {
                $error = "Failed to delete the group.";
            }
        }
    } else {
        $error = "You are not authorized to delete this group.";
    }
} else {
    $error = "Invalid group ID.";
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Delete Group - Online Study Group</title>
    <link rel="stylesheet" href="delete_group_styles.css"> <!-- Updated link for same folder -->
</head>
<body>
    <div class="delete-group-container">
        <h1>Delete Group</h1>

        <?php if (isset($error)): ?>
            <p class="error"><?= htmlspecialchars($error) ?></p>
            <a href="dashboard.php" class="btn">Back to Dashboard</a>
        <?php else: ?>
            <p>Are you sure you want to delete this group? This action cannot be undone.</p>
            <form method="POST">
                <button type="submit" name="confirm_delete" class="btn danger">Yes, Delete Group</button>
                <a href="dashboard.php" class="btn">Cancel</a>
            </form>
        <?php endif; ?>
    </div>
</body>
</html>
