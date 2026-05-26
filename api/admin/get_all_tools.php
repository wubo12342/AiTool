<?php
require_once __DIR__ . '/../cors.php';
require_once __DIR__ . '/../db.php';
require_once __DIR__ . '/_auth.php';

$data = json_decode(file_get_contents('php://input'), true);
verify_admin($data['token'] ?? '');

try {
    $stmt = $pdo->query("
        SELECT
            t.tool_ID   AS id,
            t.CID       AS cid,
            t.name,
            t.description,
            t.logo_url,
            t.website_url,
            t.video_url,
            t.pricing_plans,
            c.name AS category_name,
            COALESCE((SELECT AVG(stars) FROM tool_reviews WHERE tool_ID = t.tool_ID), 0) AS rating,
            (SELECT COUNT(*) FROM tool_reviews WHERE tool_ID = t.tool_ID) AS review_count,
            (SELECT COUNT(*) FROM user_likes   WHERE tool_ID = t.tool_ID) AS favorite_count
        FROM ai_tools t
        LEFT JOIN categories c ON t.CID = c.CID
        ORDER BY t.tool_ID DESC
    ");

    $tools = $stmt->fetchAll();
    foreach ($tools as &$tool) {
        $tool['id']             = (int)$tool['id'];
        $tool['cid']            = (int)$tool['cid'];
        $tool['rating']         = round((float)$tool['rating'], 1);
        $tool['review_count']   = (int)$tool['review_count'];
        $tool['favorite_count'] = (int)$tool['favorite_count'];
    }

    echo json_encode(['tools' => $tools]);
} catch (Throwable $e) {
    send_server_error($e);
}
