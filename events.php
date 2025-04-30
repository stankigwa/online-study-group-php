<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit();
}

include 'db_connect.php';

// Notification logic based on GET params
$notification = '';
$playSound = false;

if (isset($_GET['msg'])) {
    $msg = $_GET['msg'];
    if ($msg == 'added') {
        $notification = "✅ Event added successfully!";
        $playSound = true;
    } elseif ($msg == 'deleted') {
        $notification = "🗑️ Event deleted successfully!";
        $playSound = true;
    }
}

// Fetch all events, not just user-created
$sql = "SELECT events.*, users.username FROM events JOIN users ON events.created_by = users.id ORDER BY event_date ASC";
$result = $conn->query($sql);

if (!$result) {
    die("Error fetching events: " . $conn->error);
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Events - Online Study Group</title>
    <link rel="stylesheet" href="events_styles.css">
    
    <!-- Sound tag -->
    <audio id="notificationSound" src="sounds/mixkit-flute-mobile-phone-notification-alert-2316.wav" preload="auto"></audio>

    <script>
        // Hide notification after a few seconds
        window.onload = function () {
            const notification = document.getElementById('notification');
            if (notification) {
                setTimeout(() => notification.style.display = 'none', 5000);
            }

            <?php if ($playSound): ?>
                // Play sound if event was added or deleted
                const sound = document.getElementById("notificationSound");
                if (sound) {
                    sound.currentTime = 0;
                    sound.play().catch(error => {
                        console.warn("Sound could not play:", error);
                    });
                }
            <?php endif; ?>
        }
    </script>
</head>
<body>
    <div class="events-container">
        <h2>📅 All Group Events</h2>

        <!-- Back to Dashboard button -->
        <a href="dashboard.php" class="back-btn">← Back to Dashboard</a>

        <?php if ($notification): ?>
            <div id="notification" class="notification"><?php echo $notification; ?></div>
        <?php endif; ?>

        <a href="add_event.php" class="btn">+ Add New Event</a>

        <ul class="events-list">
            <?php if ($result->num_rows > 0): ?>
                <?php while ($event = $result->fetch_assoc()): ?>
                    <li>
                        <strong><?php echo htmlspecialchars($event['title']); ?></strong><br>
                        <small>By <?php echo htmlspecialchars($event['username']); ?> on <?php echo date("F j, Y", strtotime($event['event_date'])); ?></small><br>
                        <p><?php echo htmlspecialchars($event['description']) ?: "No description available."; ?></p>

                        <?php if ($_SESSION['user_id'] == $event['created_by']): ?>
                            <form action="delete_event.php" method="POST">
                                <input type="hidden" name="event_id" value="<?php echo $event['id']; ?>">
                                <button type="submit" class="delete-btn">Delete</button>
                            </form>
                        <?php endif; ?>
                    </li>
                <?php endwhile; ?>
            <?php else: ?>
                <li>No events found. Click above to add one!</li>
            <?php endif; ?>
        </ul>
    </div>
</body>
</html>
