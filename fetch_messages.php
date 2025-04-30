<?php
// fetch_messages.php

require_once '../config.php'; // Adjust the path if needed
session_start();

header('Content-Type: application/json');

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'error' => 'User not authenticated.']);
    exit;
}

$user_id = $_SESSION['user_id'];
$group_id = isset($_GET['group_id']) ? intval($_GET['group_id']) : 0;
$receiver_id = isset($_GET['receiver_id']) ? intval($_GET['receiver_id']) : 0;

// SQL query to fetch messages
if ($group_id > 0) {
    $sql = "SELECT m.*, u.username, u.avatar
            FROM messages m
            JOIN users u ON m.user_id = u.id
            WHERE m.group_id = ? 
            ORDER BY m.timestamp ASC";
} elseif ($receiver_id > 0) {
    $sql = "SELECT m.*, u.username, u.avatar
            FROM messages m
            JOIN users u ON m.user_id = u.id
            WHERE (m.sender_id = ? AND m.receiver_id = ?) 
               OR (m.sender_id = ? AND m.receiver_id = ?)
            ORDER BY m.timestamp ASC";
}

$stmt = $conn->prepare($sql);
if ($group_id > 0) {
    $stmt->bind_param("i", $group_id);
} else {
    $stmt->bind_param("iiii", $user_id, $receiver_id, $receiver_id, $user_id);
}

$stmt->execute();
$result = $stmt->get_result();
$messages = [];

if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $messages[] = [
            'username' => htmlspecialchars($row['username']),
            'avatar' => !empty($row['avatar']) ? htmlspecialchars($row['avatar']) : 'default_avatar.png',
            'message' => nl2br(htmlspecialchars($row['message_text'])),
            'timestamp' => date('M d, Y h:i A', strtotime($row['created_at']))
        ];
    }
}

echo json_encode(['success' => true, 'messages' => $messages]);
