<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit();
}

include 'db_connect.php';

if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST['event_id'])) {
    $event_id = $_POST['event_id'];
    $user_id = $_SESSION['user_id'];

    // Make sure only the user who created the event can delete it
    $sql = "DELETE FROM events WHERE id = ? AND created_by = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ii", $event_id, $user_id);
    
    if ($stmt->execute()) {
        $_SESSION['message'] = "Event deleted successfully!";
        $_SESSION['message_type'] = "success";
        header("Location: events.php?msg=deleted");
    } else {
        $_SESSION['message'] = "Failed to delete event.";
        $_SESSION['message_type'] = "error";
        header("Location: events.php");
    }
    exit();
}
?>
