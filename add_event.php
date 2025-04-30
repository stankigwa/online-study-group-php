<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit();
}

include 'db_connect.php';

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $title = $_POST['title'];
    $event_date = $_POST['event_date'];
    $user_id = $_SESSION['user_id'];

    // Validate input
    if (empty($title) || empty($event_date)) {
        $_SESSION['message'] = "Please fill in all fields.";
        $_SESSION['message_type'] = "error";
        header("Location: add_event.php");
        exit();
    }

    // Check if the event date is in the future
    if (strtotime($event_date) < time()) {
        $_SESSION['message'] = "Event date must be in the future.";
        $_SESSION['message_type'] = "error";
        header("Location: add_event.php");
        exit();
    }

    $sql = "INSERT INTO events (title, event_date, created_by) VALUES (?, ?, ?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ssi", $title, $event_date, $user_id);

    if ($stmt->execute()) {
        $_SESSION['message'] = "Event added successfully!";
        $_SESSION['message_type'] = "success";
        header("Location: events.php?msg=added");
    } else {
        $_SESSION['message'] = "Failed to add event.";
        $_SESSION['message_type'] = "error";
        header("Location: add_event.php");
    }
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Add Event</title>
    <link rel="stylesheet" href="add_event.css"> <!-- ✅ Updated link here -->
</head>
<body>
    <div class="form-container">
        <h2>➕ Add New Event</h2>

        <?php if (isset($_SESSION['message'])): ?>
            <div class="message <?php echo $_SESSION['message_type']; ?>">
                <?php echo $_SESSION['message']; ?>
            </div>
            <?php unset($_SESSION['message'], $_SESSION['message_type']); ?>
        <?php endif; ?>

        <form action="" method="POST">
            <label>Event Title:</label>
            <input type="text" name="title" required>

            <label>Event Date:</label>
            <input type="date" name="event_date" required>

            <button type="submit" class="btn">Add Event</button>
            <a href="events.php" class="cancel-btn">Cancel</a>
        </form>
    </div>
</body>
</html>
