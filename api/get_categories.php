<?php
// api/get_categories.php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');

require_once 'db.php';

try {
    $stmt = $pdo->query("SELECT CID as id, name FROM categories ORDER BY CID ASC");
    $categories = $stmt->fetchAll();
    echo json_encode($categories);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => $e->getMessage()]);
}
