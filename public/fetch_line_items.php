<?php
require '../private/helpers/authentication.php'; // Include authentication helper

// Ensure the user is logged in
if (!isLoggedIn()) {
    header("Location: login.php");
    exit;
}

require '../private/db/xactimate_connection.php';

// Query line items
if ($_SESSION['role'] === 'manager') {
    // Managers can see all line items
    $stmt = $pdoXactimate->query("SELECT * FROM line_items");
} else {
    // Technicians can see only their own line items
    $stmt = $pdoXactimate->prepare("SELECT * FROM line_items WHERE created_by = ?");
    $stmt->execute([$_SESSION['user_id']]);
}

$lineItems = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Fetch Line Items</title>
</head>
<body>
    <h1>Line Items</h1>
    <table border="1">
        <tr>
            <th>ID</th>
            <th>Name</th>
            <th>Description</th>
            <th>OUM</th>
            <th>Cost</th>
            <th>Category ID</th>
            <th>Created By</th>
        </tr>
        <?php foreach ($lineItems as $item): ?>
        <tr>
            <td><?php echo htmlspecialchars($item['id']); ?></td>
            <td><?php echo htmlspecialchars($item['name']); ?></td>
            <td><?php echo htmlspecialchars($item['description']); ?></td>
            <td><?php echo htmlspecialchars($item['OUM']); ?></td>
            <td><?php echo htmlspecialchars($item['cost']); ?></td>
            <td><?php echo htmlspecialchars($item['category_id']); ?></td>
            <td><?php echo htmlspecialchars($item['created_by']); ?></td>
        </tr>
        <?php endforeach; ?>
    </table>
</body>
</html>
