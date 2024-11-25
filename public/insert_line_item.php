<?php
require '../private/helpers/authentication.php'; // Include authentication helper

// Ensure the user is logged in
if (!isLoggedIn()) {
    header("Location: login.php");
    exit;
}

// Ensure only managers can access
requireRole('manager');

require '../private/db/servpro_connection.php';
require '../private/db/xactimate_connection.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = $_POST['name'];
    $description = $_POST['description'];
    $OUM = $_POST['OUM'];
    $cost = $_POST['cost'];
    $categoryId = $_POST['category_id'];
    $createdBy = $_SESSION['user_id']; // Logged-in user ID

    try {
        // Insert line item into xactimate_data4.line_items
        $stmt = $pdoXactimate->prepare("
            INSERT INTO line_items (name, description, OUM, cost, category_id, created_by)
            VALUES (?, ?, ?, ?, ?, ?)
        ");
        $stmt->execute([$name, $description, $OUM, $cost, $categoryId, $createdBy]);

        echo "Line item added successfully!";
    } catch (PDOException $e) {
        echo "Error: " . $e->getMessage();
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Insert Line Item</title>
</head>
<body>
    <h1>Insert Line Item</h1>
    <form method="POST" action="insert_line_item.php">
        <label for="name">Name:</label>
        <input type="text" id="name" name="name" required>
        <br>
        <label for="description">Description:</label>
        <textarea id="description" name="description" required></textarea>
        <br>
        <label for="OUM">Unit of Measurement (OUM):</label>
        <input type="text" id="OUM" name="OUM" required>
        <br>
        <label for="cost">Cost:</label>
        <input type="number" id="cost" name="cost" step="0.01" required>
        <br>
        <label for="category_id">Category ID:</label>
        <input type="number" id="category_id" name="category_id" required>
        <br>
        <button type="submit">Add Line Item</button>
    </form>
</body>
</html>
