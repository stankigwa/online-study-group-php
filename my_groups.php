<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit();
}

$user_id = $_SESSION['user_id'];

// TODO: Replace this array with a real database query fetching the user's groups
$groups = [
    ['id' => 1, 'name' => 'Math Study Group', 'description' => 'A group to study mathematics together.'],
    ['id' => 2, 'name' => 'History Enthusiasts', 'description' => 'Discussing historical events and theories.']
];
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>My Groups - Online Study Group</title>
    <link rel="stylesheet" href="my_groups_styles.css"> <!-- Your existing stylesheet -->
</head>
<body>
    <div class="groups-container">
        <div class="groups-header">
            <h1>My Groups</h1>
            <p>Manage your study groups, access resources, and more.</p>
        </div>

        <div class="groups-list">
            <?php if (count($groups) > 0): ?>
                <?php foreach ($groups as $group): ?>
                    <div class="group-card">
                        <h3><?php echo htmlspecialchars($group['name']); ?></h3>
                        <p><?php echo htmlspecialchars($group['description']); ?></p>
                        <div class="group-buttons">
                            <a href="chat.php?group_id=<?php echo $group['id']; ?>" class="btn main-btn">Join Chat</a>
                            <a href="video.php?group_id=<?php echo $group['id']; ?>" class="btn main-btn">Join Video</a>
                            <a href="resources.php?group_id=<?php echo $group['id']; ?>" class="btn main-btn">View Resources</a>
                            <a href="leave_group.php?group_id=<?php echo $group['id']; ?>" class="btn leave-btn">Leave Group</a>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <p>You are not part of any groups yet.</p>
            <?php endif; ?>
        </div>

        <div class="logout-btn">
            <a href="logout.php">Log Out</a>
        </div>
    </div>
</body>
</html>
