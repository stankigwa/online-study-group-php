<?php
session_start();

// Redirect to login if not logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

// Database connection
include('db_connect.php');

// If the form is submitted
if (isset($_POST['create_group'])) {
    $group_name = trim($_POST['group_name']);
    $group_description = trim($_POST['group_description']);
    $user_id = $_SESSION['user_id'];

    // Basic validation
    if (empty($group_name) || empty($group_description)) {
        $error = "Both fields are required.";
    } else {
        // Insert the group with creator's user ID
        $query = "INSERT INTO groups (name, description, created_at, user_id) VALUES (?, ?, NOW(), ?)";
        if ($stmt = $conn->prepare($query)) {
            $stmt->bind_param("ssi", $group_name, $group_description, $user_id);
            if ($stmt->execute()) {
                // Get the ID of the newly created group
                $group_id = $stmt->insert_id;

                // Add creator to group_members
                $join_group_query = "INSERT INTO group_members (user_id, group_id) VALUES (?, ?)";
                if ($stmt2 = $conn->prepare($join_group_query)) {
                    $stmt2->bind_param("ii", $user_id, $group_id);
                    if ($stmt2->execute()) {
                        $success = "Group created successfully! You are now a member of the group.";
                    } else {
                        $error = "Group created, but error adding you as a member.";
                    }
                } else {
                    $error = "Group created, but failed to prepare member join statement.";
                }
            } else {
                $error = "Error creating group: " . $stmt->error;
            }
        } else {
            $error = "Error preparing group creation query.";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Create a Study Group - Online Study Group</title>
    <link rel="stylesheet" href="create_group_styles.css">
</head>
<body>
    <div class="container">
        <h1>Create a New Study Group</h1>

        <!-- Display error or success message -->
        <?php if (isset($error)): ?>
            <div class="alert error"><?= htmlspecialchars($error) ?></div>
        <?php elseif (isset($success)): ?>
            <div class="alert success"><?= htmlspecialchars($success) ?></div>
        <?php endif; ?>

        <!-- Form for creating a group -->
        <form action="create_group.php" method="POST">
            <label for="group_name">Group Name:</label>
            <input type="text" id="group_name" name="group_name" required>

            <label for="group_description">Group Description:</label>
            <textarea id="group_description" name="group_description" required></textarea>

            <button type="submit" name="create_group">Create Group</button>
        </form>

        <p><a href="dashboard.php" class="btn-secondary">Back to Dashboard</a></p>
    </div>
</body>
</html>
