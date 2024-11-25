<?php
require '../private/helpers/authentication.php';
require '../private/db/xactimate_connection.php';
require '../vendor/autoload.php'; // Load dompdf

use Dompdf\Dompdf;

// Ensure the user is logged in
if (!isLoggedIn()) {
    header("Location: login.php");
    exit;
}

// Fetch all jobs
$stmt = $pdoXactimate->query("SELECT * FROM line_items");
$jobs = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Generate PDF content
$html = '<h1>All Entered Jobs</h1>';
$html .= '<table border="1" style="width: 100%; border-collapse: collapse;">';
$html .= '<thead><tr>';
$html .= '<th>ID</th><th>Name</th><th>Description</th><th>OUM</th><th>Cost</th><th>Category ID</th><th>Created By</th>';
$html .= '</tr></thead><tbody>';

foreach ($jobs as $job) {
    $html .= '<tr>';
    $html .= '<td>' . htmlspecialchars($job['id']) . '</td>';
    $html .= '<td>' . htmlspecialchars($job['name']) . '</td>';
    $html .= '<td>' . htmlspecialchars($job['description']) . '</td>';
    $html .= '<td>' . htmlspecialchars($job['OUM']) . '</td>';
    $html .= '<td>' . htmlspecialchars($job['cost']) . '</td>';
    $html .= '<td>' . htmlspecialchars($job['category_id']) . '</td>';
    $html .= '<td>' . htmlspecialchars($job['created_by']) . '</td>';
    $html .= '</tr>';
}

$html .= '</tbody></table>';

// Create PDF using Dompdf
$dompdf = new Dompdf();
$dompdf->loadHtml($html);
$dompdf->setPaper('A4', 'landscape');
$dompdf->render();
$dompdf->stream("jobs.pdf", ["Attachment" => true]);
