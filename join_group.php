<?php
session_start();

// Redirect to login if not logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

// Database connection
include('db_connect.php');

// Fetch all groups from the database
$query = "SELECT * FROM groups";
$result = mysqli_query($conn, $query);

// Process group joining when a group is selected
if (isset($_POST['join_group'])) {
    $group_id = $_POST['group_id'];
    $user_id = $_SESSION['user_id'];

    // Check if the user is already a member of the group
    $check_query = "SELECT * FROM group_members WHERE user_id = '$user_id' AND group_id = '$group_id'";
    $check_result = mysqli_query($conn, $check_query);

    if (mysqli_num_rows($check_result) > 0) {
        $error = "You are already a member of this group.";
    } else {
        // Add the user to the group_members table
        $insert_query = "INSERT INTO group_members (user_id, group_id) VALUES ('$user_id', '$group_id')";
        if (mysqli_query($conn, $insert_query)) {
            $success = "You have successfully joined the group!";
        } else {
            $error = "Error: " . mysqli_error($conn);
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Join Study Group - Online Study Group</title>
    <link rel="stylesheet" href="styles.css"> <!-- General Styles -->
    <link rel="stylesheet" href="join_group_styles.css"> <!-- Page Specific Styles -->
</head>
<body>
    <div class="container">
        <h1>Join a Study Group</h1>

        <!-- Display error or success message -->
        <?php if (isset($error)): ?>
            <div class="alert error"><?= $error ?></div>
        <?php elseif (isset($success)): ?>
            <div class="alert success"><?= $success ?></div>
        <?php endif; ?>

        <h2>Available Groups</h2>
        <form method="POST" action="join_group.php">
            <div class="form-group">
                <select name="group_id" required>
                    <option value="" disabled selected>Select a group to join</option>
                    <?php while ($row = mysqli_fetch_assoc($result)): ?>
                        <option value="<?= $row['id'] ?>"><?= htmlspecialchars($row['name']) ?></option>
                    <?php endwhile; ?>
                </select>
            </div>
            <div class="form-group">
                <button type="submit" name="join_group" class="btn-primary">Join Group</button>
            </div>
        </form>

        <p><a href="dashboard.php" class="back-btn">Back to Dashboard</a></p>
    </div>
</body>
</html>
