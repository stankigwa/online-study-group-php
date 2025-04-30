<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit();
}

include 'db_connect.php';

$user_id = $_SESSION['user_id'];
$username = $_SESSION['username'];

// Check if user is admin
$sql = "SELECT is_admin FROM users WHERE id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$row = $result->fetch_assoc();
$is_admin = isset($row['is_admin']) && $row['is_admin'] == 1;

// Fetch user's groups from group_members
$sql_groups = "SELECT g.id, g.name, g.user_id AS creator_id FROM groups g
               INNER JOIN group_members gm ON g.id = gm.group_id
               WHERE gm.user_id = ?";
$stmt_groups = $conn->prepare($sql_groups);
$stmt_groups->bind_param("i", $user_id);
$stmt_groups->execute();
$groups_result = $stmt_groups->get_result();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Dashboard - Online Study Group</title>
    <link rel="stylesheet" href="dashboard_styles.css">
    <style>
        .welcome-message {
            background-color: #e3fcec;
            border: 2px solid #34c38f;
            color: #2b7a4b;
            padding: 20px;
            border-radius: 12px;
            margin: 20px;
            text-align: center;
        }
        .welcome-message h2 {
            margin-bottom: 10px;
        }
        .btn.welcome-btn {
            margin-top: 15px;
            background-color: #34c38f;
            color: white;
            padding: 10px 20px;
            border: none;
            border-radius: 8px;
            text-decoration: none;
            font-weight: bold;
            display: inline-block;
        }
    </style>
</head>
<body>
<div class="dashboard-container">
    <!-- Header Section -->
    <div class="dashboard-header">
        <h1>Welcome, <?php echo htmlspecialchars($username); ?> 👋</h1>

        <?php if (isset($_GET['first_time']) && $_GET['first_time'] === 'true'): ?>
            <div class="welcome-message">
                <h2>Welcome to Your First Login!</h2>
                <p>We're excited to have you join our study group community. 🎉</p>
                <p>Start by creating or joining a group, exploring resources, or connecting with others in a video chat.</p>
                <a href="dashboard.php" class="btn welcome-btn">Got it! Show me my dashboard</a>
            </div>
        <?php else: ?>
            <p>Your Personalized Study Group Dashboard</p>
        <?php endif; ?>
    </div>

    <!-- Main Dashboard Cards -->
    <div class="dashboard-main">
        <!-- Create Group -->
        <div class="dashboard-card">
            <h3>Create New Group</h3>
            <p>Create a new study group for your peers.</p>
            <a href="create_group.php" class="btn main-btn">Create Group</a>
        </div>

        <!-- Join Group -->
        <div class="dashboard-card">
            <h3>Join a Group</h3>
            <p>Browse and join available groups.</p>
            <a href="join_group.php" class="btn main-btn">Join Group</a>
        </div>

        <!-- Your Groups from Database -->
        <div class="dashboard-card">
            <h3>Your Groups</h3>
            <ul>
                <?php if ($groups_result->num_rows > 0): ?>
                    <?php while ($group = $groups_result->fetch_assoc()): ?>
                        <li>
                            <a href="view_group.php?group_id=<?php echo $group['id']; ?>" class="btn main-btn"><?php echo htmlspecialchars($group['name']); ?></a>
                        </li>
                    <?php endwhile; ?>
                <?php else: ?>
                    <li>No groups found. Join one above!</li>
                <?php endif; ?>
            </ul>
        </div>

        <!-- Live Chat -->
        <div class="dashboard-card">
            <h3>Live Chat</h3>
            <p>Chat with your group members in real time.</p>
            <a href="chat.php" class="btn main-btn">Go to Chat</a>
        </div>

        <!-- Video Meetings -->
        <div class="dashboard-card">
            <h3>Video Meetings</h3>
            <p>Start or join a video meeting with your group.</p>
            <a href="video.php" class="btn main-btn">Go to Video</a>
        </div>

        <!-- Resources -->
        <div class="dashboard-card resources-card">
            <h3>Resources</h3>
            <p>Access and share your study materials.</p>
            <a href="resources.php" class="btn main-btn">Go to Resources</a>
        </div>

        <!-- Events -->
        <div class="dashboard-card">
            <h3>Events</h3>
            <p>View upcoming events and deadlines for your group.</p>
            <a href="events.php" class="btn main-btn">Go to Events</a>
        </div>

        <!-- Admin Panel -->
        <?php if ($is_admin): ?>
        <div class="dashboard-card">
            <h3>Admin Panel</h3>
            <p>Admin tools for managing your groups and members.</p>
            <a href="admin.php" class="btn main-btn">Go to Admin</a>
        </div>
        <?php endif; ?>
    </div>

    <!-- Logout -->
    <div class="logout-btn">
        <a href="logout.php" class="btn logout-btn">Log Out</a>
    </div>
</div>
</body>
</html>
