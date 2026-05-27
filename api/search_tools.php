<?php
// api/search_tools.php
require_once __DIR__ . '/cors.php';
require_once __DIR__ . '/db.php';
require_once __DIR__ . '/response_helper.php';

$q          = $_GET['q'] ?? '';
$categories = (isset($_GET['categories']) && $_GET['categories'] !== '') ? explode(',', $_GET['categories']) : [];
$prices     = (isset($_GET['prices'])     && $_GET['prices']     !== '') ? explode(',', $_GET['prices'])     : [];
$tags       = (isset($_GET['tags'])       && $_GET['tags']       !== '') ? array_map('intval', explode(',', $_GET['tags'])) : [];
$minRating  = isset($_GET['rating']) ? (float)$_GET['rating'] : 0;
$sort       = $_GET['sort'] ?? 'rating';

$page    = isset($_GET['page'])     ? max(1, (int)$_GET['page'])             : 1;
$perPage = isset($_GET['per_page']) ? min(100, max(1, (int)$_GET['per_page'])) : 12;
$offset  = ($page - 1) * $perPage;

try {
    $whereSql = " WHERE 1=1";
    $params   = [];

    if ($q !== '') {
        $whereSql .= " AND (t.name LIKE ? OR t.description LIKE ? OR c.name LIKE ?)";
        $params[] = "%$q%";
        $params[] = "%$q%";
        $params[] = "%$q%";
    }

    if (!empty($categories)) {
        $ph = implode(',', array_fill(0, count($categories), '?'));
        $whereSql .= " AND t.CID IN ($ph)";
        foreach ($categories as $cid) $params[] = $cid;
    }

    if (!empty($prices)) {
        $ph = implode(',', array_fill(0, count($prices), '?'));
        $whereSql .= " AND JSON_UNQUOTE(JSON_EXTRACT(t.pricing_plans, '$.status')) IN ($ph)";
        foreach ($prices as $p) $params[] = $p;
    }

    if (!empty($tags)) {
        $ph = implode(',', array_fill(0, count($tags), '?'));
        $whereSql .= " AND EXISTS (SELECT 1 FROM tool_tag_map ttm WHERE ttm.tool_ID = t.tool_ID AND ttm.TID IN ($ph))";
        foreach ($tags as $tid) $params[] = $tid;
    }

    $havingSql = $minRating > 0 ? " HAVING rating >= ?" : "";

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

    $orderClause = $sort === 'reviews' ? " ORDER BY review_count DESC" : " ORDER BY rating DESC";

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
            (SELECT COUNT(*) FROM tool_reviews WHERE tool_ID = t.tool_ID) as review_count,
            (SELECT GROUP_CONCAT(tg.name ORDER BY tg.TID SEPARATOR '||')
             FROM tool_tag_map ttm JOIN tags tg ON ttm.TID = tg.TID
             WHERE ttm.tool_ID = t.tool_ID) as feature_tags
        FROM ai_tools t
        LEFT JOIN categories c ON t.CID = c.CID
        $whereSql
        $havingSql
        $orderClause, id DESC
        LIMIT ? OFFSET ?
    ";

    $execParams = $countParams;
    $execParams[] = $perPage;
    $execParams[] = $offset;

    $stmt = $pdo->prepare($sql);
    foreach ($execParams as $i => $val) {
        $type = ($i >= count($countParams)) ? PDO::PARAM_INT : PDO::PARAM_STR;
        $stmt->bindValue($i + 1, $val, $type);
    }
    $stmt->execute();
    $tools = $stmt->fetchAll();

    foreach ($tools as &$tool) {
        $tool['id']     = (int)$tool['id'];
        $tool['rating'] = round((float)$tool['rating'], 1);

        $pricing = json_decode($tool['pricing_plans'], true);
        $tool['price_status'] = $pricing['status'] ?? 'Free';

        // tags = [分類, 定價狀態, ...功能標籤]
        $tool['tags'] = $tool['category_name'] ? [$tool['category_name']] : [];
        $tool['tags'][] = $tool['price_status'];
        if ($tool['feature_tags']) {
            foreach (explode('||', $tool['feature_tags']) as $ft) {
                $tool['tags'][] = $ft;
            }
        }
        unset($tool['feature_tags'], $tool['pricing_plans']);

        if (!$tool['logoUrl']) {
            $tool['logoUrl'] = "https://api.dicebear.com/7.x/identicon/svg?seed=" . urlencode($tool['name']);
        }
    }

    echo json_encode([
        'tools' => $tools,
        'pagination' => [
            'total_items'  => $totalItems,
            'total_pages'  => $totalPages,
            'current_page' => $page,
            'per_page'     => $perPage
        ]
    ]);

} catch (Throwable $e) {
    send_server_error($e);
}
