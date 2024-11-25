<?php
require '../private/helpers/authentication.php';
require '../private/db/xactimate_connection.php';

// Ensure the user is logged in
if (!isLoggedIn()) {
    header("Location: login.php");
    exit;
}

// Pagination variables
$itemsPerPage = 10; // Number of jobs per page
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$offset = ($page - 1) * $itemsPerPage;

// Search and filters
$search = isset($_GET['search']) ? trim($_GET['search']) : '';
$searchQuery = '';
$params = [];

// Build search query if a search term is provided
if (!empty($search)) {
    $searchQuery = " WHERE name LIKE :search OR description LIKE :search OR category_id LIKE :search";
    $params[':search'] = "%$search%";
}

// Fetch jobs with pagination and search
$query = "SELECT SQL_CALC_FOUND_ROWS * FROM line_items" . $searchQuery . " LIMIT :limit OFFSET :offset";
$stmt = $pdoXactimate->prepare($query);

// Bind values
foreach ($params as $key => $value) {
    $stmt->bindValue($key, $value);
}
$stmt->bindValue(':limit', $itemsPerPage, PDO::PARAM_INT);
$stmt->bindValue(':offset', $offset, PDO::PARAM_INT);

$stmt->execute();
$jobs = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Total items for pagination
$totalItems = $pdoXactimate->query("SELECT FOUND_ROWS()")->fetchColumn();
$totalPages = ceil($totalItems / $itemsPerPage);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>View Jobs</title>
    <style>
        table { width: 100%; border-collapse: collapse; margin-top: 20px; }
        th, td { border: 1px solid #ccc; padding: 10px; text-align: left; }
        th { background-color: #f4f4f4; }
        a { text-decoration: none; color: blue; margin-right: 10px; }
    </style>
</head>
<body>
    <h1>All Entered Jobs</h1>
    <a href="index.php">Back to Home</a>

    <!-- Search Form -->
    <form method="GET" action="view_jobs.php">
        <label for="search">Search:</label>
        <input type="text" id="search" name="search" value="<?php echo htmlspecialchars($search); ?>">
        <button type="submit">Search</button>
    </form>
    
    <!-- Export to PDF -->
    <a href="export_jobs_pdf.php">Export Jobs to PDF</a>

    <!-- Jobs Table -->
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
            </tr>
        </thead>
        <tbody>
            <?php foreach ($jobs as $job): ?>
                <tr>
                    <td><?php echo htmlspecialchars($job['id']); ?></td>
                    <td><?php echo htmlspecialchars($job['name']); ?></td>
                    <td><?php echo htmlspecialchars($job['description']); ?></td>
                    <td><?php echo htmlspecialchars($job['OUM']); ?></td>
                    <td><?php echo htmlspecialchars($job['cost']); ?></td>
                    <td><?php echo htmlspecialchars($job['category_id']); ?></td>
                    <td><?php echo htmlspecialchars($job['created_by']); ?></td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>

    <!-- Pagination -->
    <div>
        <?php for ($i = 1; $i <= $totalPages; $i++): ?>
            <a href="view_jobs.php?page=<?php echo $i; ?>&search=<?php echo urlencode($search); ?>"><?php echo $i; ?></a>
        <?php endfor; ?>
    </div>
</body>
</html>
