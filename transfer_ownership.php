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
    // Check if the logged-in user is the creator of the group
    $stmt = $conn->prepare("SELECT * FROM groups WHERE id = ? AND user_id = ?");
    $stmt->bind_param("ii", $group_id, $user_id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 1) {
        // Get all members of the group except the creator
        $stmt_members = $conn->prepare("
            SELECT users.id, users.username 
            FROM group_members 
            JOIN users ON group_members.user_id = users.id 
            WHERE group_members.group_id = ? AND users.id != ?
        ");
        $stmt_members->bind_param("ii", $group_id, $user_id);
        $stmt_members->execute();
        $members_result = $stmt_members->get_result();

        // Handle form submission
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['new_owner_id'])) {
            $new_owner_id = intval($_POST['new_owner_id']);

            // Transfer ownership
            $stmt_transfer = $conn->prepare("UPDATE groups SET user_id = ? WHERE id = ?");
            $stmt_transfer->bind_param("ii", $new_owner_id, $group_id);
            if ($stmt_transfer->execute()) {
                $success = "Ownership transferred successfully.";
            } else {
                $error = "Failed to transfer ownership.";
            }
        }
    } else {
        $error = "You are not authorized to transfer ownership of this group.";
    }
} else {
    $error = "Invalid group ID.";
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Transfer Group Ownership - Online Study Group</title>
    <link rel="stylesheet" href="transfer_ownership_styles.css"> <!-- Correct CSS link -->
</head>
<body>
    <div class="transfer-ownership-container">
        <h1>Transfer Group Ownership</h1>

        <?php if (isset($error)): ?>
            <p class="error"><?= htmlspecialchars($error) ?></p>
            <a href="dashboard.php" class="btn">Back to Dashboard</a>
        <?php elseif (isset($success)): ?>
            <p class="success"><?= htmlspecialchars($success) ?></p>
            <a href="dashboard.php" class="btn">Back to Dashboard</a>
        <?php else: ?>
            <?php if ($members_result->num_rows > 0): ?>
                <form method="POST">
                    <label for="new_owner_id">Select New Owner:</label>
                    <select name="new_owner_id" id="new_owner_id" required>
                        <?php while ($member = $members_result->fetch_assoc()): ?>
                            <option value="<?= $member['id'] ?>"><?= htmlspecialchars($member['username']) ?></option>
                        <?php endwhile; ?>
                    </select>
                    <button type="submit" class="btn primary">Transfer Ownership</button>
                    <a href="dashboard.php" class="btn">Cancel</a>
                </form>
            <?php else: ?>
                <p>No other members found to transfer ownership.</p>
                <a href="dashboard.php" class="btn">Back to Dashboard</a>
            <?php endif; ?>
        <?php endif; ?>
    </div>
</body>
</html>
