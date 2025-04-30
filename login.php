<?php
session_start();
include('db_connect.php');

if (isset($_POST['login'])) {
    $email = $_POST['email'];
    $password = $_POST['password'];

    // Check if fields are empty
    if (empty($email) || empty($password)) {
        $error = "All fields are required.";
    } else {
        // Fetch user from the database
        $query = "SELECT * FROM users WHERE email = '$email'";
        $result = mysqli_query($conn, $query);

        if ($result && mysqli_num_rows($result) == 1) {
            $user = mysqli_fetch_assoc($result);

            // Verify password
            if (password_verify($password, $user['password'])) {
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['username'] = $user['username'];

                // Check if it's the first login
                if ($user['first_login'] == 1) {
                    // Update first_login to 0 after the first login
                    $update_query = "UPDATE users SET first_login = 0 WHERE id = {$user['id']}";
                    mysqli_query($conn, $update_query);

                    // Redirect to the dashboard with a special query parameter for first-time login
                    header("Location: dashboard.php?first_time=true");
                    exit;
                } else {
                    // Regular login (not first time)
                    header("Location: dashboard.php");
                    exit;
                }
            } else {
                $error = "Invalid email or password.";
            }
        } else {
            $error = "Invalid email or password.";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Online Study Group</title>
    <link rel="stylesheet" href="login_styles.css">
</head>
<body>
    <div class="container">
        <h1>Login to Study Group</h1>

        <?php if (isset($error)): ?>
            <div class="alert error"><?= $error ?></div>
        <?php endif; ?>

        <form method="POST" action="login.php">
            <label for="email">Email:</label>
            <input type="email" name="email" required>

            <label for="password">Password:</label>
            <input type="password" name="password" required>

            <button type="submit" name="login">Login</button>
        </form>

        <p class="register-link">Don't have an account? <a href="register.php">Register</a></p>
    </div>
</body>
</html>
