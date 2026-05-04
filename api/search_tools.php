<?php
// api/search_tools.php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');

require_once 'db.php';

$q = isset($_GET['q']) ? $_GET['q'] : '';
$categories = isset($_GET['categories']) && $_GET['categories'] !== '' ? explode(',', $_GET['categories']) : [];
$prices = isset($_GET['prices']) && $_GET['prices'] !== '' ? explode(',', $_GET['prices']) : [];
$minRating = isset($_GET['rating']) ? (float)$_GET['rating'] : 0;

try {
    $sql = "
        SELECT 
            t.tool_ID as id, 
            t.name, 
            t.description, 
            t.logo_url as logoUrl, 
            t.website_url,
            t.pricing_plans,
            c.name as category_name,
            COALESCE((SELECT AVG(stars) FROM tool_reviews WHERE tool_ID = t.tool_ID), 4.5) as rating
        FROM ai_tools t
        LEFT JOIN categories c ON t.CID = c.CID
        WHERE 1=1
    ";
    
    $params = [];
    
    if ($q) {
        $sql .= " AND (t.name LIKE :q OR t.description LIKE :q)";
        $params['q'] = "%$q%";
    }
    
    if (!empty($categories)) {
        $placeholders = [];
        foreach ($categories as $i => $catId) {
            $key = "cat_$i";
            $placeholders[] = ":$key";
            $params[$key] = $catId;
        }
        $sql .= " AND t.CID IN (" . implode(',', $placeholders) . ")";
    }
    
    // 價格方案篩選
    if (!empty($prices)) {
        $priceConditions = [];
        foreach ($prices as $i => $price) {
            $key = "price_$i";
            $priceConditions[] = "JSON_EXTRACT(t.pricing_plans, '$.status') = :$key";
            $params[$key] = $price;
        }
        $sql .= " AND (" . implode(' OR ', $priceConditions) . ")";
    }
    
    if ($minRating > 0) {
        $sql .= " HAVING rating >= :rating";
        $params['rating'] = $minRating;
    }
    
    $sql .= " ORDER BY id DESC";
    
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $tools = $stmt->fetchAll();

    foreach ($tools as &$tool) {
        $tool['id'] = (int)$tool['id'];
        $tool['rating'] = round((float)$tool['rating'], 1);
        $tool['tags'] = [$tool['category_name']];
        
        $pricing = json_decode($tool['pricing_plans'], true);
        $tool['price_status'] = $pricing['status'] ?? 'Free';
        $tool['tags'][] = $tool['price_status']; // 將價格也放入標籤
        
        if (!$tool['logoUrl']) {
            $tool['logoUrl'] = "https://api.dicebear.com/7.x/identicon/svg?seed=" . $tool['name'];
        }
    }

    echo json_encode($tools);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => $e->getMessage()]);
}
