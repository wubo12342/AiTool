<?php
// api/tool.php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');

require_once 'db.php';

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($id <= 0) {
    http_response_code(400);
    echo json_encode(['error' => '無效的 ID']);
    exit;
}

try {
    $query = "
        SELECT 
            t.tool_ID as id, 
            t.name, 
            t.description, 
            t.logo_url as logoUrl, 
            t.website_url as officialUrl,
            t.video_url,
            t.pricing_plans,
            c.name as category_name,
            COALESCE((SELECT AVG(stars) FROM tool_reviews WHERE tool_ID = t.tool_ID), 4.5) as rating
        FROM ai_tools t
        LEFT JOIN categories c ON t.CID = c.CID
        WHERE t.tool_ID = :id
    ";

    $stmt = $pdo->prepare($query);
    $stmt->execute(['id' => $id]);
    $tool = $stmt->fetch();

    if ($tool) {
        $tool['id'] = (int)$tool['id'];
        $tool['rating'] = round((float)$tool['rating'], 1);
        $tool['tags'] = [$tool['category_name']]; // 暫時用分類當標籤

        if (!$tool['logoUrl']) {
            $tool['logoUrl'] = "https://api.dicebear.com/7.x/identicon/svg?seed=" . $tool['name'];
        }

        // 處理價格方案 JSON
        $plansData = json_decode($tool['pricing_plans'], true);
        $tool['plans'] = isset($plansData['plans']) ? $plansData['plans'] : [];

        // 為了相容前端，將 plans 裡的 price 補上符號（如果需要的話）
        foreach ($tool['plans'] as &$plan) {
            if (is_numeric($plan['price']) && $plan['price'] != '0') {
                $currency = isset($plansData['currency']) ? $plansData['currency'] : '$';
                $plan['price'] = $currency . $plan['price'] . (isset($plansData['status']) && $plansData['status'] == 'Subscription' ? ' / 月' : '');
            } else if ($plan['price'] == '0') {
                $plan['price'] = '免費';
            }
        }

        unset($tool['pricing_plans']);
        echo json_encode($tool);
    } else {
        http_response_code(404);
        echo json_encode(['error' => '找不到工具']);
    }
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => $e->getMessage()]);
}
