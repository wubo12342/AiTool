<?php
header('Content-Type: application/json');
require_once 'config.php';

$report = [
    'stage' => 'start',
    'env_vars' => [
        'DB_HOST' => getenv('DB_HOST'),
        'DB_NAME' => getenv('DB_NAME'),
        'DB_USER' => getenv('DB_USER'),
        'DB_PASS' => getenv('DB_PASS') ? '***' : 'EMPTY'
    ],
    'constants' => [
        'DB_HOST' => DB_HOST,
        'DB_NAME' => DB_NAME,
        'DB_USER' => DB_USER
    ]
];

try {
    $report['stage'] = 'connecting';
    $db = getDB();
    $report['stage'] = 'connected';
    
    $stmt = $db->query("SELECT 1");
    $report['stage'] = 'query_success';
    
    $stmt = $db->query("SHOW TABLES");
    $report['tables'] = $stmt->fetchAll(PDO::FETCH_COLUMN);
    
    echo json_encode(['status' => 'success', 'data' => $report]);
} catch (Exception $e) {
    echo json_encode([
        'status' => 'error',
        'stage' => $report['stage'],
        'message' => $e->getMessage(),
        'debug' => $report
    ]);
}
