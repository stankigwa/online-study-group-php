<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit();
}

$user_id = $_SESSION['user_id'];

// Example notification data, you would fetch this from your database
$notifications = [
    ['type' => 'Message', 'message' => 'New message in Math group.', 'timestamp' => '2025-04-15 12:30:00', 'read' => false],
    ['type' => 'Resource', 'message' => 'New resource uploaded to Physics group.', 'timestamp' => '2025-04-15 14:00:00', 'read' => true],
    ['type' => 'Event', 'message' => 'New event created in Chemistry group.', 'timestamp' => '2025-04-14 10:00:00', 'read' => false],
];

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['mark_read'])) {
    // Update notification as read (replace with actual DB update logic)
    // Example: markNotificationAsRead($notification_id);
    echo "Notification marked as read!";
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Notifications - Online Study Group</title>
    <link rel="stylesheet" href="notifications_styles.css"> <!-- Link to your specific CSS file -->
</head>
<body>
    <div class="notifications-container">
        <div class="notifications-header">
            <h1>Notifications</h1>
        </div>

        <div class="notifications-list">
            <?php foreach ($notifications as $notification): ?>
                <div class="notification <?php echo $notification['read'] ? 'read' : 'unread'; ?>">
                    <div class="notification-type"><?php echo htmlspecialchars($notification['type']); ?></div>
                    <div class="notification-message"><?php echo htmlspecialchars($notification['message']); ?></div>
                    <div class="notification-time"><?php echo htmlspecialchars($notification['timestamp']); ?></div>

                    <?php if (!$notification['read']): ?>
                        <form action="notifications.php" method="POST">
                            <input type="hidden" name="mark_read" value="1">
                            <button type="submit" class="btn mark-read-btn">Mark as Read</button>
                        </form>
                    <?php endif; ?>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</body>
</html>
