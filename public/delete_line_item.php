<?php
require '../private/helpers/authentication.php'; // Include authentication helper
require '../private/db/xactimate_connection.php'; // Database connection for line items

// Ensure the user is logged in
if (!isLoggedIn()) {
    header("Location: login.php");
    exit;
}

// Ensure only managers can access
requireRole('manager');

// Validate and process the deletion request
if (isset($_GET['id']) && is_numeric($_GET['id'])) {
    $id = (int) $_GET['id']; // Cast to integer to prevent SQL injection

    try {
        // Prepare and execute deletion query
        $stmt = $pdoXactimate->prepare("DELETE FROM line_items WHERE id = ?");
        $stmt->execute([$id]);

        // Redirect back to the dashboard with success message
        header("Location: dashboard.php?message=Line item deleted successfully");
        exit;
    } catch (PDOException $e) {
        // Redirect with error message
        header("Location: dashboard.php?error=" . urlencode("Error deleting line item: " . $e->getMessage()));
        exit;
    }
} else {
    // Redirect with error if ID is invalid or missing
    header("Location: dashboard.php?error=" . urlencode("Invalid line item ID"));
    exit;
}
?>
