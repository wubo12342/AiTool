<?php
// api/search_tools.php
require_once __DIR__ . '/cors.php';
require_once __DIR__ . '/db.php';
require_once __DIR__ . '/response_helper.php';

$q = $_GET['q'] ?? '';
$categories = (isset($_GET['categories']) && $_GET['categories'] !== '') ? explode(',', $_GET['categories']) : [];
$prices = (isset($_GET['prices']) && $_GET['prices'] !== '') ? explode(',', $_GET['prices']) : [];
$minRating = isset($_GET['rating']) ? (float)$_GET['rating'] : 0;
$sort = $_GET['sort'] ?? 'rating';

$page = isset($_GET['page']) ? max(1, (int)$_GET['page']) : 1;
$perPage = isset($_GET['per_page']) ? min(100, max(1, (int)$_GET['per_page'])) : 12;
$offset = ($page - 1) * $perPage;

try {
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

    $havingSql = "";
    if ($minRating > 0) {
        $havingSql = " HAVING rating >= ?";
    }

    // 計算總筆數
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
    $totalPages = max(1, (int)ceil($totalItems / $perPage));

    $orderClause = " ORDER BY rating DESC";
    if ($sort === 'reviews') $orderClause = " ORDER BY review_count DESC";

    // S6 — LIMIT/OFFSET 改用 bindValue
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
        LIMIT :limit OFFSET :offset
    ";

    $stmt = $pdo->prepare($sql);
    foreach ($countParams as $i => $val) {
        $stmt->bindValue($i + 1, $val);
    }
    $stmt->bindValue(':limit', $perPage, PDO::PARAM_INT);
    $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
    $stmt->execute();
    $tools = $stmt->fetchAll();

    foreach ($tools as &$tool) {
        $tool['id'] = (int)$tool['id'];
        $tool['rating'] = round((float)$tool['rating'], 1);
        $tool['tags'] = $tool['category_name'] ? [$tool['category_name']] : [];

        $pricing = json_decode($tool['pricing_plans'], true);
        $tool['price_status'] = $pricing['status'] ?? 'Free';
        $tool['tags'][] = $tool['price_status'];

        if (!$tool['logoUrl']) {
            $tool['logoUrl'] = "https://api.dicebear.com/7.x/identicon/svg?seed=" . urlencode($tool['name']);
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

} catch (Throwable $e) {
    send_server_error($e);
}
