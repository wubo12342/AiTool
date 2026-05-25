<?php
// api/get_categories.php
require_once __DIR__ . '/cors.php';
require_once __DIR__ . '/db.php';
require_once __DIR__ . '/response_helper.php';

try {
    $stmt = $pdo->query("SELECT CID as id, name FROM categories ORDER BY CID ASC");
    echo json_encode($stmt->fetchAll());
} catch (Throwable $e) {
    send_server_error($e);
}
