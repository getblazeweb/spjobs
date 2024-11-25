<?php
require '../private/helpers/authentication.php'; // Include authentication helper

// Check if the user is logged in
if (!isLoggedIn()) {
    header("Location: login.php"); // Redirect to login if not authenticated
    exit;
}

// Fetch the user's role
$userRole = $_SESSION['role'];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Job Management System</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 20px;
        }
        a {
            text-decoration: none;
            color: blue;
            font-size: 18px;
            margin-right: 20px;
        }
        h1 {
            color: #333;
        }
        .role-info {
            color: #555;
        }
    </style>
</head>
<body>
    <h1>Welcome to the Job Management System</h1>
    <p class="role-info">Logged in as: <strong><?php echo htmlspecialchars($_SESSION['username']); ?></strong> (Role: <strong><?php echo htmlspecialchars($userRole); ?></strong>)</p>
    
    <nav>
        <a href="view_jobs.php">View Jobs</a>
        <?php if ($userRole === 'manager'): ?>
            <a href="dashboard.php">Manage Line Items</a>
            <a href="create_user.php">Manage Users</a>
        <?php endif; ?>
        <a href="logout.php">Logout</a>
    </nav>
    
    <footer>
        <p>© <?php echo date('Y'); ?> Your Company. All rights reserved.</p>
    </footer>
</body>
</html>
