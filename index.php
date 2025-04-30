<?php
session_start();
include('db_connect.php');

if (isset($_SESSION['user_id'])) {
    header("Location: dashboard.php"); // Redirect to dashboard if already logged in
    exit();
}

$username = $password = "";
$username_err = $password_err = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = trim($_POST["username"]);
    $password = trim($_POST["password"]);

    if (empty($username)) {
        $username_err = "Please enter your username.";
    }
    if (empty($password)) {
        $password_err = "Please enter your password.";
    }

    if (empty($username_err) && empty($password_err)) {
        $sql = "SELECT id, username, password FROM users WHERE username = ?";
        if ($stmt = $conn->prepare($sql)) {
            $stmt->bind_param("s", $username);
            if ($stmt->execute()) {
                $stmt->store_result();
                if ($stmt->num_rows == 1) {
                    $stmt->bind_result($id, $username, $hashed_password);
                    if ($stmt->fetch()) {
                        if (password_verify($password, $hashed_password)) {
                            $_SESSION['user_id'] = $id;
                            $_SESSION['username'] = $username;
                            header("Location: dashboard.php");
                            exit();
                        } else {
                            $password_err = "Invalid password.";
                        }
                    }
                } else {
                    $username_err = "No account found with that username.";
                }
            } else {
                echo "Something went wrong. Please try again later.";
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Study Hub</title>
    <link rel="stylesheet" href="styles.css">
</head>
<body>
    <div class="login-container">
        <h1>Welcome to Study Hub</h1>
        
        <form action="index.php" method="POST">
            <div class="form-group">
                <label for="username">Username:</label>
                <input type="text" id="username" name="username" value="<?php echo htmlspecialchars($username); ?>" required>
                <span class="error"><?php echo $username_err; ?></span>
            </div>

            <div class="form-group">
                <label for="password">Password:</label>
                <input type="password" id="password" name="password" required>
                <span class="error"><?php echo $password_err; ?></span>
            </div>

            <button type="submit">Login</button>
        </form>

        <p>Don't have an account? <a href="register.php">Register here</a></p>
    </div>
</body>
</html>
