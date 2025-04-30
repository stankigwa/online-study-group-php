<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit();
}

include 'db_connection.php';

$user_id = $_SESSION['user_id'];

// Check if user is admin
$sql = "SELECT is_admin FROM users WHERE id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$row = $result->fetch_assoc();
$is_admin = isset($row['is_admin']) && $row['is_admin'] == 1;

if (!$is_admin) {
    header("Location: dashboard.php");
    exit();
}

// Fetch users for management
$sql_users = "SELECT id, username FROM users";
$stmt_users = $conn->prepare($sql_users);
$stmt_users->execute();
$result_users = $stmt_users->get_result();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Admin Panel - Manage Users</title>
    <link rel="stylesheet" href="dashboard_styles.css">
    <script>
        // Confirmation dialogs
        function confirmRemoval(username) {
            return confirm("Are you sure you want to remove '" + username + "' from the group?");
        }

        function confirmPromotion(username) {
            return confirm("Promote '" + username + "' to admin?");
        }
    </script>
</head>
<body>
    <div class="admin-container">
        <div class="admin-header">
            <h1>Welcome, Admin 👑</h1>
            <p>Manage users and groups below</p>
        </div>

        <!-- Display success or error message -->
        <?php if (isset($_GET['message'])): ?>
            <div class="message"><?php echo htmlspecialchars($_GET['message']); ?></div>
        <?php endif; ?>

        <!-- Manage Users -->
        <div class="admin-section">
            <h2>Manage Users</h2>
            <table class="admin-table">
                <thead>
                    <tr>
                        <th>User ID</th>
                        <th>Username</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($user = $result_users->fetch_assoc()): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($user['id']); ?></td>
                            <td><?php echo htmlspecialchars($user['username']); ?></td>
                            <td>
                                <a href="remove_user.php?user_id=<?php echo $user['id']; ?>"
                                   class="btn remove-btn"
                                   onclick="return confirmRemoval('<?php echo htmlspecialchars($user['username']); ?>');">
                                   Remove from Group
                                </a>
                                <?php if ($user['id'] != $user_id): ?>
                                    <a href="make_admin.php?user_id=<?php echo $user['id']; ?>"
                                       class="btn promote-btn"
                                       onclick="return confirmPromotion('<?php echo htmlspecialchars($user['username']); ?>');">
                                       Promote to Admin
                                    </a>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>

        <!-- Logout Button -->
        <div class="logout-btn">
            <a href="logout.php" class="btn logout-btn">Log Out</a>
        </div>
    </div>
</body>
</html>
