<?php
// api/search_tools.php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');

require_once 'db.php';

$q = isset($_GET['q']) ? $_GET['q'] : '';
$categories = isset($_GET['categories']) && $_GET['categories'] !== '' ? explode(',', $_GET['categories']) : [];
$prices = isset($_GET['prices']) && $_GET['prices'] !== '' ? explode(',', $_GET['prices']) : [];
$minRating = isset($_GET['rating']) ? (float)$_GET['rating'] : 0;
$sort = $_GET['sort'] ?? 'rating';

// 分頁參數
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$perPage = isset($_GET['per_page']) ? (int)$_GET['per_page'] : 12;
$offset = ($page - 1) * $perPage;

try {
    // 1. 基本查詢語句 (不含 LIMIT)
    $whereSql = " WHERE 1=1";
    $params = [];
    
    if ($q !== '') {
        $whereSql .= " AND (t.name LIKE ? OR t.description LIKE ? OR c.name LIKE ?)";
        $params[] = "%$q%";
        $params[] = "%$q%";
        $params[] = "%$q%";
    }
    
    if (!empty($categories)) {
        $placeholders = str_repeat('?,', count($categories) - 1) . '?';
        $whereSql .= " AND t.CID IN ($placeholders)";
        foreach ($categories as $catId) {
            $params[] = $catId;
        }
    }
    
    // 評分篩選 (HAVING 比較難算總數，我們改用子查詢或 WHERE)
    $havingSql = "";
    if ($minRating > 0) {
        $havingSql = " HAVING rating >= ?";
    }

    // 2. 獲取總筆數 (為了分頁計算)
    $countSql = "
        SELECT COUNT(*) FROM (
            SELECT 
                t.tool_ID,
                COALESCE((SELECT AVG(stars) FROM tool_reviews WHERE tool_ID = t.tool_ID), 0) as rating
            FROM ai_tools t
            LEFT JOIN categories c ON t.CID = c.CID
            $whereSql
            $havingSql
        ) as total_count
    ";
    
    $countParams = $params;
    if ($minRating > 0) $countParams[] = $minRating;
    
    $countStmt = $pdo->prepare($countSql);
    $countStmt->execute($countParams);
    $totalItems = (int)$countStmt->fetchColumn();
    $totalPages = ceil($totalItems / $perPage);

    // 3. 獲取當前頁面的工具
    $orderClause = " ORDER BY rating DESC";
    if ($sort === 'reviews') $orderClause = " ORDER BY review_count DESC";
    
    $sql = "
        SELECT 
            t.tool_ID as id, 
            t.name, 
            t.description, 
            t.logo_url as logoUrl, 
            t.website_url,
            t.pricing_plans,
            c.name as category_name,
            COALESCE((SELECT AVG(stars) FROM tool_reviews WHERE tool_ID = t.tool_ID), 0) as rating,
            (SELECT COUNT(*) FROM tool_reviews WHERE tool_ID = t.tool_ID) as review_count
        FROM ai_tools t
        LEFT JOIN categories c ON t.CID = c.CID
        $whereSql
        $havingSql
        $orderClause, id DESC
        LIMIT $perPage OFFSET $offset
    ";
    
    $stmt = $pdo->prepare($sql);
    $stmt->execute($countParams);
    $tools = $stmt->fetchAll();

    foreach ($tools as &$tool) {
        $tool['id'] = (int)$tool['id'];
        $tool['rating'] = round((float)$tool['rating'], 1);
        $tool['tags'] = [$tool['category_name']];
        
        $pricing = json_decode($tool['pricing_plans'], true);
        $tool['price_status'] = $pricing['status'] ?? 'Free';
        $tool['tags'][] = $tool['price_status'];
        
        if (!$tool['logoUrl']) {
            $tool['logoUrl'] = "https://api.dicebear.com/7.x/identicon/svg?seed=" . $tool['name'];
        }
    }

    echo json_encode([
        'tools' => $tools,
        'pagination' => [
            'total_items' => $totalItems,
            'total_pages' => $totalPages,
            'current_page' => $page,
            'per_page' => $perPage
        ]
    ]);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => $e->getMessage()]);
}
