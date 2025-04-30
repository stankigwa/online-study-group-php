<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit();
}

include 'db_connect.php';

$user_id = $_SESSION['user_id'];
$group_id = isset($_GET['group_id']) ? intval($_GET['group_id']) : 0;

// Fetch group name from database
$group_name = "Group";
$stmt_group = $conn->prepare("SELECT name FROM groups WHERE id = ?");
$stmt_group->bind_param("i", $group_id);
$stmt_group->execute();
$result_group = $stmt_group->get_result();
if ($row = $result_group->fetch_assoc()) {
    $group_name = $row['name'];
}

// Handle file upload
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['resource_file'])) {
    $file_name = $_FILES['resource_file']['name'];
    $file_tmp = $_FILES['resource_file']['tmp_name'];
    $upload_folder = "resources/";
    $file_path = $upload_folder . basename($file_name);

    if (!is_dir($upload_folder)) {
        mkdir($upload_folder, 0777, true);
    }

    if (move_uploaded_file($file_tmp, $file_path)) {
        $stmt_insert = $conn->prepare("INSERT INTO uploads (user_id, file_name, file_path) VALUES (?, ?, ?)");
        $stmt_insert->bind_param("iss", $user_id, $file_name, $file_path);
        $stmt_insert->execute();

        echo "<p style='color: green; text-align: center;'>Resource uploaded successfully!</p>";
    } else {
        echo "<p style='color: red; text-align: center;'>Failed to upload resource.</p>";
    }
}

// Handle delete request
if (isset($_GET['delete']) && is_numeric($_GET['delete'])) {
    $delete_id = intval($_GET['delete']);
    
    // Fetch file path to delete the file
    $stmt = $conn->prepare("SELECT file_path FROM uploads WHERE id = ? AND user_id = ?");
    $stmt->bind_param("ii", $delete_id, $user_id);
    $stmt->execute();
    $res = $stmt->get_result();
    if ($file = $res->fetch_assoc()) {
        // Delete from filesystem
        if (file_exists($file['file_path'])) {
            unlink($file['file_path']);
        }
        // Delete from database
        $stmt_del = $conn->prepare("DELETE FROM uploads WHERE id = ? AND user_id = ?");
        $stmt_del->bind_param("ii", $delete_id, $user_id);
        $stmt_del->execute();

        echo "<p style='color: green; text-align: center;'>Resource deleted successfully!</p>";
    }
}

// Fetch uploaded resources
$stmt_resources = $conn->prepare("
    SELECT uploads.id, uploads.file_name, uploads.file_path, uploads.uploaded_at, users.username, uploads.user_id 
    FROM uploads 
    JOIN users ON uploads.user_id = users.id 
    ORDER BY uploads.uploaded_at DESC
");
$stmt_resources->execute();
$result_resources = $stmt_resources->get_result();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Resources - <?php echo htmlspecialchars($group_name); ?> - Online Study Group</title>
    <link rel="stylesheet" href="resources_styles.css">
    <script>
        function confirmDelete(deleteId) {
            const confirmation = confirm("Are you sure you want to delete this resource?");
            if (confirmation) {
                window.location.href = 'resources.php?delete=' + deleteId;
            }
        }
    </script>
</head>
<body>
<div class="resources-container">
    <div class="resources-header">
        <h1>Resources - <?php echo htmlspecialchars($group_name); ?></h1>
    </div>

    <div class="upload-section">
        <h2>Upload a New Resource</h2>
        <form action="resources.php?group_id=<?php echo $group_id; ?>" method="POST" enctype="multipart/form-data" class="upload-form">
            <input type="file" name="resource_file" required>
            <button type="submit" class="btn upload-btn">Upload Resource</button>
        </form>
    </div>

    <div class="resources-list">
        <h2>Shared Resources</h2>
        <?php if ($result_resources->num_rows > 0): ?>
            <ul>
                <?php while ($row = $result_resources->fetch_assoc()): ?>
                    <li>
                        <a href="<?php echo htmlspecialchars($row['file_path']); ?>" download class="resource-link">
                            <?php echo htmlspecialchars($row['file_name']); ?>
                        </a>
                        <br>
                        <small>
                            Uploaded by <strong><?php echo htmlspecialchars($row['username']); ?></strong> on 
                            <?php echo htmlspecialchars($row['uploaded_at']); ?>
                        </small>
                        <?php if ($row['user_id'] == $user_id): ?>
                            <!-- Show delete button only for uploader -->
                            <button class="btn delete-btn" onclick="confirmDelete(<?php echo $row['id']; ?>)">Delete</button>
                        <?php endif; ?>
                    </li>
                <?php endwhile; ?>
            </ul>
        <?php else: ?>
            <p>No resources uploaded yet.</p>
        <?php endif; ?>
    </div>

    <!-- Back to Dashboard button -->
    <div class="back-btn-container">
        <a href="dashboard.php" class="btn back-btn">Back to Dashboard</a>
    </div>
</div>
</body>
</html>
