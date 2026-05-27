<?php
// api/tool.php
require_once __DIR__ . '/cors.php';
require_once __DIR__ . '/db.php';
require_once __DIR__ . '/response_helper.php';

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($id <= 0) {
    send_bad_request('無效的 ID');
}

try {
    $query = "
        SELECT
            t.tool_ID as id,
            t.CID as cid,
            t.name,
            t.description,
            t.logo_url as logoUrl,
            t.website_url as officialUrl,
            t.video_url,
            t.pricing_plans,
            c.name as category_name,
            COALESCE((SELECT AVG(stars) FROM tool_reviews WHERE tool_ID = t.tool_ID), 0) as rating,
            (SELECT GROUP_CONCAT(tg.name ORDER BY tg.TID SEPARATOR '||')
             FROM tool_tag_map ttm JOIN tags tg ON ttm.TID = tg.TID
             WHERE ttm.tool_ID = t.tool_ID) as feature_tags
        FROM ai_tools t
        LEFT JOIN categories c ON t.CID = c.CID
        WHERE t.tool_ID = :id
    ";

    $stmt = $pdo->prepare($query);
    $stmt->execute(['id' => $id]);
    $tool = $stmt->fetch();

    if (!$tool) {
        send_not_found('找不到工具');
    }

    $tool['id'] = (int)$tool['id'];
    $tool['cid'] = (int)$tool['cid'];
    $tool['rating'] = round((float)$tool['rating'], 1);

    if (!$tool['logoUrl']) {
        $tool['logoUrl'] = "https://api.dicebear.com/7.x/identicon/svg?seed=" . urlencode($tool['name']);
    }

    $plansData = json_decode($tool['pricing_plans'], true);
    $tool['plans'] = isset($plansData['plans']) ? $plansData['plans'] : [];
    $tool['pricing_status'] = $plansData['status'] ?? null;

    // Build tags: [category, pricing_status, ...feature_tags]
    $tool['tags'] = $tool['category_name'] ? [$tool['category_name']] : [];
    if ($tool['pricing_status']) {
        $tool['tags'][] = $tool['pricing_status'];
    }
    if ($tool['feature_tags']) {
        foreach (explode('||', $tool['feature_tags']) as $ft) {
            if ($ft !== '') $tool['tags'][] = $ft;
        }
    }
    unset($tool['feature_tags']);
    $tool['pricing_last_check'] = $plansData['last_check'] ?? null;
    $tool['pricing_currency'] = $plansData['currency'] ?? 'USD';

    $hasFree = false;
    $lowestPaid = null;
    foreach ($tool['plans'] as &$plan) {
        if (!isset($plan['price'])) {
            $plan['price_raw'] = null;
            $plan['price'] = '—';
            continue;
        }
        $plan['price_raw'] = is_numeric($plan['price']) ? (float)$plan['price'] : null;
        if (is_numeric($plan['price']) && $plan['price'] != '0') {
            $currency = $plansData['currency'] ?? '$';
            $suffix = (isset($plansData['status']) && $plansData['status'] == 'Subscription') ? ' / 月' : '';
            $plan['price'] = $currency . $plan['price'] . $suffix;
            if ($lowestPaid === null || (float)$plan['price_raw'] < $lowestPaid) {
                $lowestPaid = (float)$plan['price_raw'];
            }
        } else if ($plan['price'] == '0') {
            $plan['price'] = '免費';
            $hasFree = true;
        }
    }
    unset($plan);
    $tool['has_free_plan'] = $hasFree;
    $tool['lowest_paid_price'] = $lowestPaid;

    unset($tool['pricing_plans']);
    echo json_encode($tool);

} catch (Throwable $e) {
    send_server_error($e);
}
