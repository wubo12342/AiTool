<?php
// api/get_category_tools.php
require_once __DIR__ . '/cors.php';
require_once __DIR__ . '/db.php';
require_once __DIR__ . '/response_helper.php';

$cid = isset($_GET['cid']) ? (int)$_GET['cid'] : 0;

if ($cid <= 0) {
    send_bad_request('無效的分類 ID');
}

try {
    $catStmt = $pdo->prepare("SELECT * FROM categories WHERE CID = ?");
    $catStmt->execute([$cid]);
    $category = $catStmt->fetch();

    if (!$category) {
        send_not_found('找不到該分類');
    }

    $query = "
        SELECT
            t.tool_ID as id,
            t.name,
            t.description,
            t.logo_url as logoUrl,
            t.website_url,
            c.name as category_name,
            COALESCE((SELECT AVG(stars) FROM tool_reviews WHERE tool_ID = t.tool_ID), 0) as rating
        FROM ai_tools t
        LEFT JOIN categories c ON t.CID = c.CID
        WHERE t.CID = :cid
        ORDER BY t.tool_ID DESC
    ";

    $stmt = $pdo->prepare($query);
    $stmt->execute(['cid' => $cid]);
    $tools = $stmt->fetchAll();

    foreach ($tools as &$tool) {
        $tool['id'] = (int)$tool['id'];
        $tool['rating'] = round((float)$tool['rating'], 1);
        $tool['tags'] = $tool['category_name'] ? [$tool['category_name']] : [];
        if (!$tool['logoUrl']) {
            $tool['logoUrl'] = "https://api.dicebear.com/7.x/identicon/svg?seed=" . urlencode($tool['name']);
        }
    }

    echo json_encode(['category' => $category, 'tools' => $tools]);

} catch (Throwable $e) {
    send_server_error($e);
}
