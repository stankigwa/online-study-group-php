<?php
session_start();
include('db_connection.php');

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit();
}

$current_user_id = $_SESSION['user_id'];

// Check if the user is an admin (based on is_admin field)
$sql_check_admin = "SELECT is_admin FROM users WHERE id = ?";
$stmt_check = $conn->prepare($sql_check_admin);
$stmt_check->bind_param("i", $current_user_id);
$stmt_check->execute();
$result_check = $stmt_check->get_result();
$row = $result_check->fetch_assoc();

if (!$row || $row['is_admin'] != 1) {
    header("Location: dashboard.php");
    exit();
}

// Validate and sanitize target user ID
if (!isset($_GET['user_id']) || !is_numeric($_GET['user_id'])) {
    header("Location: admin.php?message=" . urlencode("Invalid user ID."));
    exit();
}

$promote_user_id = intval($_GET['user_id']);

// Prevent self-promotion redundancy
if ($promote_user_id === $current_user_id) {
    header("Location: admin.php?message=" . urlencode("You are already an admin."));
    exit();
}

// Promote user to admin
$sql_promote = "UPDATE users SET is_admin = 1 WHERE id = ?";
$stmt_promote = $conn->prepare($sql_promote);
$stmt_promote->bind_param("i", $promote_user_id);
$stmt_promote->execute();

// Optional: Log the promotion
$sql_log = "INSERT INTO action_logs (admin_id, action_type, target_user_id, action_time) VALUES (?, 'promote_admin', ?, NOW())";
$stmt_log = $conn->prepare($sql_log);
$stmt_log->bind_param("ii", $current_user_id, $promote_user_id);
$stmt_log->execute();

// Redirect with message
if ($stmt_promote->affected_rows > 0) {
    $message = "User promoted to admin successfully.";
} else {
    $message = "User is already an admin or not found.";
}

header("Location: admin.php?message=" . urlencode($message));
exit();
?>
