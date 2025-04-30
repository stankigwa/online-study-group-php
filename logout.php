<?php
session_start();
session_unset();
session_destroy();

// Redirect to login page after logging out
header("Location: index.php");
exit;
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Logged Out - Online Study Group</title>
    <link rel="stylesheet" href="logout_styles.css">
</head>
<body>
    <div class="logout-container">
        <div class="logout-card">
            <h1>You’ve been logged out</h1>
            <p>Thank you for using the Online Study Group tool.</p>
            <a href="index.php" class="btn go-home-btn">Go to Login</a>
        </div>
    </div>
</body>
</html>
