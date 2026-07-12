<?php
header('Content-Type: application/json');
include '../includes/config.php';

$total_items = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) AS count FROM items"))['count'] ?? 0;
$matched_items = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) AS count FROM items WHERE status='matched'"))['count'] ?? 0;
$total_claims = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) AS count FROM claims"))['count'] ?? 0;

echo json_encode([
    'total_items' => (int)$total_items,
    'matched_items' => (int)$matched_items,
    'total_claims' => (int)$total_claims
]);
?>