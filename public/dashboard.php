<?php
require '../private/helpers/authentication.php'; // Include authentication helper
require '../private/db/xactimate_connection.php'; // Database connection for line items

// Restrict access to managers only
requireRole('manager');

$error = null;
$success = null;

// Handle Create and Update Form Submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = $_POST['id'] ?? null; // Null for Create, value for Update
    $name = $_POST['name'];
    $description = $_POST['description'];
    $OUM = $_POST['OUM'];
    $cost = $_POST['cost'];
    $categoryId = $_POST['category_id'];
    $createdBy = $_SESSION['user_id']; // Logged-in user ID

    try {
        if ($id) {
            // Update existing line item
            $stmt = $pdoXactimate->prepare("
                UPDATE line_items
                SET name = ?, description = ?, OUM = ?, cost = ?, category_id = ?
                WHERE id = ?
            ");
            $stmt->execute([$name, $description, $OUM, $cost, $categoryId, $id]);
            $success = "Line item updated successfully!";
        } else {
            // Create new line item
            $stmt = $pdoXactimate->prepare("
                INSERT INTO line_items (name, description, OUM, cost, category_id, created_by)
                VALUES (?, ?, ?, ?, ?, ?)
            ");
            $stmt->execute([$name, $description, $OUM, $cost, $categoryId, $createdBy]);
            $success = "Line item created successfully!";
        }
    } catch (PDOException $e) {
        $error = "Error: " . $e->getMessage();
    }
}

// Handle Deletion
if (isset($_GET['delete_id'])) {
    $deleteId = $_GET['delete_id'];
    try {
        $stmt = $pdoXactimate->prepare("DELETE FROM line_items WHERE id = ?");
        $stmt->execute([$deleteId]);
        $success = "Line item deleted successfully!";
    } catch (PDOException $e) {
        $error = "Error deleting line item: " . $e->getMessage();
    }
}

// Fetch all line items for managers
$stmt = $pdoXactimate->query("SELECT * FROM line_items");
$lineItems = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Line Item Management</title>
</head>
<body>
    <h1>Manage Line Items</h1>
    <?php if ($error): ?>
        <p style="color: red;"><?php echo $error; ?></p>
    <?php endif; ?>
    <?php if ($success): ?>
        <p style="color: green;"><?php echo $success; ?></p>
    <?php endif; ?>
    <a href="index.php">Back to Home</a>
    <table>
        <thead>
            <tr>
                <th>ID</th>
                <th>Name</th>
                <th>Description</th>
                <th>OUM</th>
                <th>Cost</th>
                <th>Category ID</th>
                <th>Created By</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($lineItems as $item): ?>
                <tr>
                    <td><?php echo htmlspecialchars($item['id']); ?></td>
                    <td><?php echo htmlspecialchars($item['name']); ?></td>
                    <td><?php echo htmlspecialchars($item['description']); ?></td>
                    <td><?php echo htmlspecialchars($item['OUM']); ?></td>
                    <td><?php echo htmlspecialchars($item['cost']); ?></td>
                    <td><?php echo htmlspecialchars($item['category_id']); ?></td>
                    <td><?php echo htmlspecialchars($item['created_by']); ?></td>
                    <td>
                        <a href="dashboard.php?id=<?php echo $item['id']; ?>">Edit</a>
                        <a href="dashboard.php?delete_id=<?php echo $item['id']; ?>" onclick="return confirm('Are you sure you want to delete this line item?');">Delete</a>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</body>
</html>
