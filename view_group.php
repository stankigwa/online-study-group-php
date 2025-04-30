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

// Fetch group details
$sql_group = "SELECT g.id, g.name, g.description, g.user_id AS creator_id FROM groups g WHERE g.id = ?";
$stmt_group = $conn->prepare($sql_group);
$stmt_group->bind_param("i", $group_id);
$stmt_group->execute();
$group_result = $stmt_group->get_result();

if ($group_result->num_rows == 0) {
    header("Location: dashboard.php");
    exit();
}

$group = $group_result->fetch_assoc();
$is_creator = ($group['creator_id'] == $user_id);

// Fetch group members
$sql_members = "SELECT u.id, u.username FROM users u
                INNER JOIN group_members gm ON u.id = gm.user_id
                WHERE gm.group_id = ?";
$stmt_members = $conn->prepare($sql_members);
$stmt_members->bind_param("i", $group_id);
$stmt_members->execute();
$members_result = $stmt_members->get_result();

// Check if a live call is ongoing
$sql_call = "SELECT is_live FROM group_calls WHERE group_id = ?";
$stmt_call = $conn->prepare($sql_call);
$stmt_call->bind_param("i", $group_id);
$stmt_call->execute();
$result_call = $stmt_call->get_result();

$is_call_live = false;
if ($row_call = $result_call->fetch_assoc()) {
    $is_call_live = ($row_call['is_live'] == 1);
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Group Details - <?php echo htmlspecialchars($group['name']); ?></title>
    <link rel="stylesheet" href="view_group_styles.css">
    <script>
        function confirmLeave(groupId) {
            if (confirm("Are you sure you want to leave this group?")) {
                window.location.href = "leave_group.php?group_id=" + groupId;
            }
        }
    </script>
</head>
<body>
    <div class="group-container">
        <div class="group-header">
            <h1><?php echo htmlspecialchars($group['name']); ?></h1>
            <p><?php echo htmlspecialchars($group['description']); ?></p>
            <p><strong>Creator ID:</strong> <?php echo htmlspecialchars($group['creator_id']); ?></p>
        </div>

        <div class="group-actions">
            <?php if ($is_creator): ?>
                <a href="delete_group.php?group_id=<?php echo $group_id; ?>" class="btn delete-btn">Delete Group</a>
                <a href="remove_user.php?group_id=<?php echo $group_id; ?>" class="btn remove-user-btn">Remove User</a>
                <a href="transfer_ownership.php?group_id=<?php echo $group_id; ?>" class="btn transfer-btn">Transfer Ownership</a>
                <a href="start_video_call.php?group_id=<?php echo $group_id; ?>" class="btn start-call-btn">Start Video Call</a>
            <?php else: ?>
                <?php if ($is_call_live): ?>
                    <a href="join_video_call.php?group_id=<?php echo $group_id; ?>" class="btn join-call-btn">
                        Join Video Call
                    </a>
                    <span class="live-now-badge">🔴 Live Now</span>
                <?php else: ?>
                    <p class="no-call-msg">No active video call in this group right now.</p>
                <?php endif; ?>
            <?php endif; ?>
            <button onclick="confirmLeave(<?php echo $group_id; ?>)" class="btn leave-btn">Leave Group</button>
        </div>

        <div class="group-members">
            <h3>Members</h3>
            <ul>
                <?php while ($member = $members_result->fetch_assoc()): ?>
                    <li><?php echo htmlspecialchars($member['username']); ?></li>
                <?php endwhile; ?>
            </ul>
        </div>

        <div class="back-btn">
            <a href="dashboard.php" class="btn back-to-dashboard-btn">← Back to Dashboard</a>
        </div>
    </div>
</body>
</html>
