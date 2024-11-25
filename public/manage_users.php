<?php
require '../private/helpers/authentication.php';
require '../private/db/servpro_connection.php'; // Database connection for users

// Restrict access to managers only
requireRole('manager');

$error = null;
$success = null;

// Handle Add or Update User Form Submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = $_POST['id'] ?? null; // Null for Add, value for Update
    $username = trim($_POST['username']);
    $password = $_POST['password'];
    $role = $_POST['role'];

    try {
        if ($id) {
            // Update existing user
            $stmt = $pdoServpro->prepare("
                UPDATE users
                SET username = ?, role = ?
                WHERE id = ?
            ");
            $stmt->execute([$username, $role, $id]);

            // If a new password is provided, update it
            if (!empty($password)) {
                $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
                $stmt = $pdoServpro->prepare("UPDATE users SET password = ? WHERE id = ?");
                $stmt->execute([$hashedPassword, $id]);
            }

            $success = "User updated successfully!";
        } else {
            // Add new user
            $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
            $stmt = $pdoServpro->prepare("
                INSERT INTO users (username, password, role)
                VALUES (?, ?, ?)
            ");
            $stmt->execute([$username, $hashedPassword, $role]);
            $success = "User added successfully!";
        }
    } catch (PDOException $e) {
        $error = "Error: " . $e->getMessage();
    }
}

// Handle Archive Request
if (isset($_GET['archive_id'])) {
    $archiveId = $_GET['archive_id'];

    // Prevent managers from archiving themselves
    if ($archiveId == $_SESSION['user_id']) {
        $error = "You cannot archive yourself!";
    } else {
        try {
            $stmt = $pdoServpro->prepare("UPDATE users SET archived = 1 WHERE id = ?");
            $stmt->execute([$archiveId]);
            $success = "User archived successfully!";
        } catch (PDOException $e) {
            $error = "Error archiving user: " . $e->getMessage();
        }
    }
}

// Fetch all active users
$stmt = $pdoServpro->query("SELECT id, username, role FROM users WHERE archived = 0");
$users = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Fetch archived users if requested
$archivedUsers = [];
if (isset($_GET['view_archived'])) {
    $stmt = $pdoServpro->query("SELECT id, username, role FROM users WHERE archived = 1");
    $archivedUsers = $stmt->fetchAll(PDO::FETCH_ASSOC);
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Users</title>
    <style>
        table { width: 100%; border-collapse: collapse; margin-top: 20px; }
        th, td { border: 1px solid #ccc; padding: 10px; text-align: left; }
        th { background-color: #f4f4f4; }
        a { text-decoration: none; color: blue; margin-right: 10px; }
    </style>
</head>
<body>
    <h1>Manage Users</h1>
    <?php if ($error): ?>
        <p style="color: red;"><?php echo $error; ?></p>
    <?php endif; ?>
    <?php if ($success): ?>
        <p style="color: green;"><?php echo $success; ?></p>
    <?php endif; ?>

    <h2>Add or Update User</h2>
    <form method="POST" action="manage_users.php">
        <input type="hidden" name="id" value="<?php echo isset($_GET['id']) ? htmlspecialchars($_GET['id']) : ''; ?>">
        <label for="username">Username:</label>
        <input type="text" id="username" name="username" required>
        <br>
        <label for="password">Password:</label>
        <input type="password" id="password" name="password">
        <small>Leave blank to keep the current password (for updates only).</small>
        <br>
        <label for="role">Role:</label>
        <select id="role" name="role" required>
            <option value="manager">Manager</option>
            <option value="technician">Technician</option>
        </select>
        <br>
        <button type="submit"><?php echo isset($_GET['id']) ? 'Update' : 'Add'; ?> User</button>
    </form>

    <h2>Active Users</h2>
    <table>
        <thead>
            <tr>
                <th>ID</th>
                <th>Username</th>
                <th>Role</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($users as $user): ?>
                <tr>
                    <td><?php echo htmlspecialchars($user['id']); ?></td>
                    <td><?php echo htmlspecialchars($user['username']); ?></td>
                    <td><?php echo htmlspecialchars($user['role']); ?></td>
                    <td>
                        <a href="manage_users.php?id=<?php echo $user['id']; ?>">Edit</a>
                        <a href="manage_users.php?archive_id=<?php echo $user['id']; ?>" onclick="return confirm('Are you sure you want to archive this user?');">Archive</a>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>

    <h2>Archived Users</h2>
    <a href="manage_users.php?view_archived=1">View Archived Users</a>
    <?php if (!empty($archivedUsers)): ?>
        <table>
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Username</th>
                    <th>Role</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($archivedUsers as $user): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($user['id']); ?></td>
                        <td><?php echo htmlspecialchars($user['username']); ?></td>
                        <td><?php echo htmlspecialchars($user['role']); ?></td>
                        <td>
                            <a href="manage_users.php?restore_id=<?php echo $user['id']; ?>">Restore</a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php endif; ?>
    <a href="index.php">Back to Home</a>
</body>
</html>
