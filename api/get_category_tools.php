<?php
// api/get_category_tools.php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');

require_once 'db.php';

$cid = isset($_GET['cid']) ? (int)$_GET['cid'] : 0;

if ($cid <= 0) {
    http_response_code(400);
    echo json_encode(['error' => '無效的分類 ID']);
    exit;
}

try {
    // 獲取分類資訊
    $catStmt = $pdo->prepare("SELECT * FROM categories WHERE CID = ?");
    $catStmt->execute([$cid]);
    $category = $catStmt->fetch();

    if (!$category) {
        http_response_code(404);
        echo json_encode(['error' => '找不到該分類']);
        exit;
    }

    // 獲取該分類下的工具
    $query = "
        SELECT 
            t.tool_ID as id, 
            t.name, 
            t.description, 
            t.logo_url as logoUrl, 
            t.website_url,
            c.name as category_name,
            COALESCE((SELECT AVG(stars) FROM tool_reviews WHERE tool_ID = t.tool_ID), 4.5) as rating
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
        $tool['tags'] = [$tool['category_name']];
        if (!$tool['logoUrl']) {
            $tool['logoUrl'] = "https://api.dicebear.com/7.x/identicon/svg?seed=" . $tool['name'];
        }
    }

    echo json_encode([
        'category' => $category,
        'tools' => $tools
    ]);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => $e->getMessage()]);
}
