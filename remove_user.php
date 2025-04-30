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

// Step 1: Verify group exists and user is the creator
$stmt = $conn->prepare("SELECT * FROM groups WHERE id = ? AND user_id = ?");
$stmt->bind_param("ii", $group_id, $user_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    $error = "You are not authorized to manage this group or the group doesn't exist.";
} else {
    // Step 2: If a member ID is submitted for removal
    if (isset($_GET['remove_id']) && is_numeric($_GET['remove_id'])) {
        $remove_id = intval($_GET['remove_id']);

        // Prevent creator from removing themselves
        if ($remove_id === $user_id) {
            $error = "You cannot remove yourself from your own group.";
        } else {
            // Remove the member from the group
            $stmt_delete = $conn->prepare("DELETE FROM group_members WHERE user_id = ? AND group_id = ?");
            $stmt_delete->bind_param("ii", $remove_id, $group_id);
            $stmt_delete->execute();

            if ($stmt_delete->affected_rows > 0) {
                $message = "Member removed successfully.";
            } else {
                $error = "User was not a member of this group.";
            }
        }
    }

    // Step 3: Fetch all current members of the group (excluding the creator)
    $stmt_members = $conn->prepare("
        SELECT users.id, users.username 
        FROM users 
        JOIN group_members ON users.id = group_members.user_id 
        WHERE group_members.group_id = ? AND users.id != ?
    ");
    $stmt_members->bind_param("ii", $group_id, $user_id);
    $stmt_members->execute();
    $members_result = $stmt_members->get_result();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Remove Member - Online Study Group</title>
    <link rel="stylesheet" href="remove_user_styles.css">
    <script>
        function confirmRemoval(username, url) {
            if (confirm("Are you sure you want to remove " + username + " from the group?")) {
                window.location.href = url;
            }
        }
    </script>
</head>
<body>
    <div class="remove-user-container">
        <h2>Manage Group Members</h2>

        <?php if ($error): ?>
            <div class="alert error"><?php echo htmlspecialchars($error); ?></div>
        <?php elseif ($message): ?>
            <div class="alert success"><?php echo htmlspecialchars($message); ?></div>
        <?php endif; ?>

        <?php if (isset($members_result) && $members_result->num_rows > 0): ?>
            <table class="members-table">
                <thead>
                    <tr>
                        <th>Username</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($row = $members_result->fetch_assoc()): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($row['username']); ?></td>
                            <td>
                                <button class="remove-btn"
                                        onclick="confirmRemoval('<?php echo addslashes($row['username']); ?>', 'remove_user.php?group_id=<?php echo $group_id; ?>&remove_id=<?php echo $row['id']; ?>')">
                                    Remove
                                </button>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        <?php else: ?>
            <p>No other members in this group.</p>
        <?php endif; ?>

        <div class="back-link">
            <a href="view_group.php?group_id=<?php echo $group_id; ?>" class="btn">Back to Group</a>
        </div>
    </div>
</body>
</html>
