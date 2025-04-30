<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit();
}

include 'db_connect.php';

$user_id = $_SESSION['user_id'];
$group_id = isset($_GET['group_id']) ? intval($_GET['group_id']) : 0;
$receiver_id = isset($_GET['receiver_id']) ? intval($_GET['receiver_id']) : 0;

$group_name = "Chat";

// Get name for display
if ($group_id > 0) {
    $stmt_group = $conn->prepare("SELECT name FROM groups WHERE id = ?");
    $stmt_group->bind_param("i", $group_id);
    $stmt_group->execute();
    $result_group = $stmt_group->get_result();
    if ($row = $result_group->fetch_assoc()) {
        $group_name = "Group: " . $row['name'];
    }
} elseif ($receiver_id > 0) {
    $stmt_receiver = $conn->prepare("SELECT username FROM users WHERE id = ?");
    $stmt_receiver->bind_param("i", $receiver_id);
    $stmt_receiver->execute();
    $result_receiver = $stmt_receiver->get_result();
    if ($row = $result_receiver->fetch_assoc()) {
        $group_name = "Private Chat with " . $row['username'];
    }
}

// Handle new message
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['message'])) {
    $message = trim($_POST['message']);
    if (!empty($message)) {
        if ($group_id > 0) {
            $stmt = $conn->prepare("INSERT INTO messages (sender_id, receiver_id, group_id, message_text) VALUES (?, NULL, ?, ?)");
            $stmt->bind_param("iis", $user_id, $group_id, $message);
            $stmt->execute();
        } elseif ($receiver_id > 0) {
            $stmt = $conn->prepare("INSERT INTO messages (sender_id, receiver_id, group_id, message_text) VALUES (?, ?, NULL, ?)");
            $stmt->bind_param("iis", $user_id, $receiver_id, $message);
            $stmt->execute();
        }
    }
    exit(); // End for AJAX
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title><?php echo htmlspecialchars($group_name); ?> - Chat</title>
    <link rel="stylesheet" href="chat_styles.css">
    <script src="https://cdn.jsdelivr.net/npm/emoji-picker-element@^1/index.js" type="module"></script>
    <script>
        function loadMessages() {
            const xhr = new XMLHttpRequest();
            xhr.open("GET", "fetch_messages.php?group_id=<?= $group_id ?>&receiver_id=<?= $receiver_id ?>", true);
            xhr.onload = function () {
                if (xhr.status === 200) {
                    const messages = JSON.parse(xhr.responseText);
                    const messagesArea = document.querySelector(".messages-area");
                    messagesArea.innerHTML = ''; // Clear previous messages
                    messages.messages.forEach(msg => {
                        const messageDiv = document.createElement('div');
                        messageDiv.classList.add('message');
                        messageDiv.innerHTML = `
                            <div class="message-avatar">
                                <img src="avatars/${msg.avatar}" alt="${msg.username}'s Avatar" class="message-avatar-img">
                            </div>
                            <div class="message-content">
                                <strong>${msg.username}</strong>
                                <span>${msg.timestamp}</span>
                                <p>${msg.message}</p>
                            </div>
                        `;
                        messagesArea.appendChild(messageDiv);
                    });
                    messagesArea.scrollTop = messagesArea.scrollHeight;
                }
            };
            xhr.send();
        }

        function sendMessage(event) {
            event.preventDefault();
            const messageInput = document.querySelector('input[name="message"]');
            const message = messageInput.value;
            if (message.trim() === '') return;
            const xhr = new XMLHttpRequest();
            xhr.open("POST", "chat.php?group_id=<?= $group_id ?>&receiver_id=<?= $receiver_id ?>", true);
            xhr.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
            xhr.onload = function () {
                messageInput.value = '';
                loadMessages();
            };
            xhr.send("message=" + encodeURIComponent(message));
        }

        document.addEventListener("DOMContentLoaded", function () {
            loadMessages();
            setInterval(loadMessages, 3000); // Poll for new messages every 3 seconds
            document.querySelector(".chat-form").addEventListener("submit", sendMessage);
        });
    </script>
</head>
<body>
    <div class="chat-background"></div>
    <div class="chat-container">
        <div class="chat-header">
            <h1><?php echo htmlspecialchars($group_name); ?></h1>
        </div>

        <!-- Back to Dashboard Button -->
        <a href="dashboard.php" class="back-to-dashboard">Back to Dashboard</a>

        <div class="messages-area"></div>

        <form method="POST" class="chat-form">
            <input type="text" name="message" placeholder="Type a message... 😊" required>
            <button type="submit">Send</button>
        </form>
    </div>
</body>
</html>
