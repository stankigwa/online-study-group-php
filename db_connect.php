<?php
// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

$servername = "127.0.0.1";
$username = "root";
$password = "Wifi2020";
$dbname = "study_group_tool";
$port = 3307;

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname, $port);

// Check connection
if ($conn->connect_error) {
    // Log the error to a file if there's a connection failure
    error_log("Connection failed: " . $conn->connect_error, 3, "error_log.txt");
    die("Sorry, we're experiencing technical issues.");
} else {
    // Uncomment the following line to test connection success
    // echo "Connected successfully!";
}
?>
