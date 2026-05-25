<?php
// api/get_tools.php
require_once __DIR__ . '/cors.php';
require_once __DIR__ . '/db.php';
require_once __DIR__ . '/response_helper.php';

$keyword = $_GET['keyword'] ?? '';
$cid = $_GET['cid'] ?? '';
$sort = $_GET['sort'] ?? 'likes';

try {
    $sql = "
        SELECT
            t.tool_ID as id,
            t.name,
            t.description,
            t.logo_url as logoUrl,
            t.website_url,
            c.name as category_name,
            COALESCE((SELECT AVG(stars) FROM tool_reviews WHERE tool_ID = t.tool_ID), 0) as rating,
            (SELECT COUNT(*) FROM tool_reviews WHERE tool_ID = t.tool_ID) as review_count,
            (SELECT COUNT(*) FROM user_likes WHERE tool_ID = t.tool_ID) as usage_count
        FROM ai_tools t
        LEFT JOIN categories c ON t.CID = c.CID
        WHERE 1=1
    ";

    $params = [];

    if ($keyword !== '') {
        $sql .= " AND (t.name LIKE ? OR t.description LIKE ? OR c.name LIKE ?)";
        $params[] = "%$keyword%";
        $params[] = "%$keyword%";
        $params[] = "%$keyword%";
    }

    if ($cid !== '') {
        $sql .= " AND t.CID = ?";
        $params[] = $cid;
    }

    switch ($sort) {
        case 'rating':
            $sql .= " ORDER BY rating DESC, t.tool_ID DESC";
            break;
        case 'reviews':
            $sql .= " ORDER BY review_count DESC, t.tool_ID DESC";
            break;
        case 'likes':
        default:
            $sql .= " ORDER BY usage_count DESC, t.tool_ID DESC";
            break;
    }

    // $limit 只會是 8 或 20（非用戶輸入），直接插入 SQL 是安全的
    // 這樣可以避免 PDO 混用 positional ? 和 named :limit 造成 query 失敗
    $limit = ($keyword === '' && $cid === '') ? 8 : 20;
    $sql .= " LIMIT $limit";

    $stmt = $pdo->prepare($sql);
    foreach ($params as $i => $val) {
        $stmt->bindValue($i + 1, $val);
    }
    $stmt->execute();
    $tools = $stmt->fetchAll();

    foreach ($tools as &$tool) {
        $tool['id'] = (int)$tool['id'];
        $tool['rating'] = round((float)$tool['rating'], 1);
        $tool['tags'] = $tool['category_name'] ? [$tool['category_name']] : [];
        if (!$tool['logoUrl']) {
            $tool['logoUrl'] = "https://api.dicebear.com/7.x/identicon/svg?seed=" . urlencode($tool['name']);
        }
    }

    echo json_encode(['tools' => $tools]);

} catch (Throwable $e) {
    send_server_error($e);
}
