<?php
// api/get_tools.php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');

require_once 'db.php';

error_reporting(E_ALL);
ini_set('display_errors', 1);

$keyword = $_GET['keyword'] ?? '';
$cid = $_GET['cid'] ?? '';
$sort = $_GET['sort'] ?? 'likes'; // 預設依最愛排序

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

    // 處理排序
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
    
    $limit = ($keyword === '' && $cid === '') ? 8 : 20;
    $sql .= " LIMIT $limit";

    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $tools = $stmt->fetchAll();

    foreach ($tools as &$tool) {
        $tool['id'] = (int)$tool['id'];
        $tool['rating'] = round((float)$tool['rating'], 1);
        $tool['tags'] = [$tool['category_name']];
        if (!$tool['logoUrl']) {
            $tool['logoUrl'] = "https://api.dicebear.com/7.x/identicon/svg?seed=" . $tool['name'];
        }
    }

    // 增加一個 debug 欄位，讓我們知道後端收到了什麼
    $debug = [
        'received_keyword' => $keyword,
        'received_cid' => $cid,
        'received_sort' => $sort,
        'sql_limit' => $limit
    ];

    echo json_encode([
        'debug' => $debug,
        'tools' => $tools
    ]);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => $e->getMessage()]);
}
