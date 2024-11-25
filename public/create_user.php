<?php
require '../private/db/servpro_connection.php'; // Database connection

$error = null;
$success = null;

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username']);
    $password = $_POST['password'];
    $role = $_POST['role'];

    if (!empty($username) && !empty($password) && in_array($role, ['manager', 'technician'])) {
        try {
            // Hash password and insert user
            $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
            $stmt = $pdoServpro->prepare("INSERT INTO users (username, password, role) VALUES (?, ?, ?)");
            $stmt->execute([$username, $hashedPassword, $role]);
            $success = "User created successfully!";
        } catch (PDOException $e) {
            $error = "Error creating user: " . $e->getMessage();
        }
    } else {
        $error = "All fields are required, and role must be valid.";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Create User</title>
</head>
<body>
    <h1>Create New User</h1>
    <?php if ($error): ?>
        <p style="color: red;"><?php echo $error; ?></p>
    <?php endif; ?>
    <?php if ($success): ?>
        <p style="color: green;"><?php echo $success; ?></p>
    <?php endif; ?>
    <form method="POST" action="create_user.php">
        <label for="username">Username:</label>
        <input type="text" id="username" name="username" required>
        <br>
        <label for="password">Password:</label>
        <input type="password" id="password" name="password" required>
        <br>
        <label for="role">Role:</label>
        <select id="role" name="role" required>
            <option value="manager">Manager</option>
            <option value="technician">Technician</option>
        </select>
        <br>
        <button type="submit">Create User</button>
    </form>
</body>
</html>