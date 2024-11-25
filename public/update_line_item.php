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

$error = null;
$success = null;

// Fetch the existing data for the line item (if an ID is provided)
$lineItem = null;
if (isset($_GET['id'])) {
    $lineItemId = $_GET['id'];
    $stmt = $pdoXactimate->prepare("SELECT * FROM line_items WHERE id = ?");
    $stmt->execute([$lineItemId]);
    $lineItem = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$lineItem) {
        $error = "Line item not found.";
    }
}

// Handle form submission for updating the line item
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = $_POST['id'];
    $name = $_POST['name'];
    $description = $_POST['description'];
    $OUM = $_POST['OUM'];
    $cost = $_POST['cost'];
    $categoryId = $_POST['category_id'];

    try {
        // Update the line item in xactimate_data4.line_items
        $stmt = $pdoXactimate->prepare("
            UPDATE line_items
            SET name = ?, description = ?, OUM = ?, cost = ?, category_id = ?
            WHERE id = ?
        ");
        $stmt->execute([$name, $description, $OUM, $cost, $categoryId, $id]);

        $success = "Line item updated successfully!";
    } catch (PDOException $e) {
        $error = "Error updating line item: " . $e->getMessage();
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Update Line Item</title>
</head>
<body>
    <h1>Update Line Item</h1>
    <?php if ($error): ?>
        <p style="color: red;"><?php echo $error; ?></p>
    <?php endif; ?>
    <?php if ($success): ?>
        <p style="color: green;"><?php echo $success; ?></p>
    <?php endif; ?>

    <?php if ($lineItem): ?>
        <form method="POST" action="update_line_item.php">
            <input type="hidden" name="id" value="<?php echo htmlspecialchars($lineItem['id']); ?>">

            <label for="name">Name:</label>
            <input type="text" id="name" name="name" value="<?php echo htmlspecialchars($lineItem['name']); ?>" required>
            <br>

            <label for="description">Description:</label>
            <textarea id="description" name="description" required><?php echo htmlspecialchars($lineItem['description']); ?></textarea>
            <br>

            <label for="OUM">Unit of Measurement (OUM):</label>
            <input type="text" id="OUM" name="OUM" value="<?php echo htmlspecialchars($lineItem['OUM']); ?>" required>
            <br>

            <label for="cost">Cost:</label>
            <input type="number" id="cost" name="cost" step="0.01" value="<?php echo htmlspecialchars($lineItem['cost']); ?>" required>
            <br>

            <label for="category_id">Category ID:</label>
            <input type="number" id="category_id" name="category_id" value="<?php echo htmlspecialchars($lineItem['category_id']); ?>" required>
            <br>

            <button type="submit">Update Line Item</button>
        </form>
    <?php else: ?>
        <p>No line item selected for update. Please go back and try again.</p>
    <?php endif; ?>
</body>
</html>