<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit();
}

include 'db_connect.php';

$user_id = $_SESSION['user_id'];
$group_id = isset($_GET['group_id']) ? intval($_GET['group_id']) : 0;

$message = "";
$error = "";

// Step 1: Make sure group ID is valid
if ($group_id > 0) {
    // Step 2: Check if the user is the creator of the group
    $stmt_creator = $conn->prepare("SELECT user_id FROM groups WHERE id = ?");
    $stmt_creator->bind_param("i", $group_id);
    $stmt_creator->execute();
    $creator_result = $stmt_creator->get_result();

    if ($creator_result->num_rows > 0) {
        $creator_data = $creator_result->fetch_assoc();
        $creator_id = $creator_data['user_id'];

        // Prevent creator from leaving their own group
        if ($creator_id == $user_id) {
            $error = "You cannot leave a group you created. Please transfer ownership or delete the group.";
        } else {
            // Step 3: Check if the user is a member
            $stmt_member = $conn->prepare("SELECT * FROM group_members WHERE user_id = ? AND group_id = ?");
            $stmt_member->bind_param("ii", $user_id, $group_id);
            $stmt_member->execute();
            $result_member = $stmt_member->get_result();

            if ($result_member->num_rows > 0) {
                // Step 4: Remove the user from the group
                $stmt_delete = $conn->prepare("DELETE FROM group_members WHERE user_id = ? AND group_id = ?");
                $stmt_delete->bind_param("ii", $user_id, $group_id);
                if ($stmt_delete->execute()) {
                    $message = "You have successfully left the group.";
                } else {
                    $error = "An error occurred while trying to leave the group.";
                }
            } else {
                $error = "You are not a member of this group.";
            }
        }
    } else {
        $error = "Group not found.";
    }
} else {
    $error = "Invalid group ID.";
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Leave Group - Online Study Group</title>
    <link rel="stylesheet" href="leave_group_styles.css">
</head>
<body>
    <div class="leave-group-container">
        <div class="leave-group-header">
            <h1>Leave Group Confirmation</h1>
        </div>

        <?php if ($error): ?>
            <div class="alert error"><?php echo htmlspecialchars($error); ?></div>
        <?php elseif ($message): ?>
            <div class="alert success"><?php echo htmlspecialchars($message); ?></div>
            <div class="return-link">
                <a href="dashboard.php" class="btn">Return to Dashboard</a>
            </div>
        <?php else: ?>
            <div class="leave-group-section">
                <p>Are you sure you want to leave this group?</p>
                <form action="leave_group.php" method="GET">
                    <input type="hidden" name="group_id" value="<?php echo htmlspecialchars($group_id); ?>">
                    <button type="submit" class="btn leave-group-btn">Yes, Leave Group</button>
                    <a href="dashboard.php" class="btn cancel-btn">Cancel</a>
                </form>
            </div>
        <?php endif; ?>
    </div>
</body>
</html>
