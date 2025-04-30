<?php
// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Include the database connection
include('db_connect.php');

// Check if the form is submitted
if (isset($_POST['register'])) {
    $username = $_POST['username'];
    $email = $_POST['email'];
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];

    // Basic validation
    if (empty($username) || empty($email) || empty($password) || empty($confirm_password)) {
        $error = "All fields are required.";
    } elseif ($password != $confirm_password) {
        $error = "Passwords do not match.";
    } else {
        // Hash the password
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);
        
        // Insert user into the database
        $query = "INSERT INTO users (username, email, password) VALUES ('$username', '$email', '$hashed_password')";
        
        if (mysqli_query($conn, $query)) {
            // Option 1: Redirect to login.php
            // header("Location: login.php");
            // exit;

            // Option 2: Show success message
            $success = "Registration successful! You can now <a href='login.php'>log in</a>.";
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
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register - Online Study Group</title>
    <link rel="stylesheet" href="styles.css">
    <link rel="stylesheet" href="register_styles.css">
</head>
<body>
    <div class="container">
        <h1>Register for Online Study Group</h1>
        
        <!-- Display error or success message -->
        <?php if (isset($error)): ?>
            <div class="alert error"><?= $error ?></div>
        <?php elseif (isset($success)): ?>
            <div class="alert success"><?= $success ?></div>
        <?php endif; ?>
        
        <!-- Registration Form -->
        <form action="register.php" method="POST">
            <label for="username">Username:</label>
            <input type="text" id="username" name="username" required>
            
            <label for="email">Email:</label>
            <input type="email" id="email" name="email" required>
            
            <label for="password">Password:</label>
            <input type="password" id="password" name="password" required>
            
            <label for="confirm_password">Confirm Password:</label>
            <input type="password" id="confirm_password" name="confirm_password" required>
            
            <button type="submit" name="register">Register</button>
        </form>
        
        <p class="login-link">Already have an account? <a href="login.php">Log in</a></p>
    </div>
</body>
</html>
