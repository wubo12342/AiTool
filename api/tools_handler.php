<?php
require_once 'config.php';

/**
 * 智慧搜尋工具
 */
function search_tools($args) {
    $db = getDB();
    $category = $args['category'] ?? null;
    $keyword = $args['keyword'] ?? '';

    $sql = "SELECT t.tool_ID, t.name, t.description, t.website_url, t.pricing_plans, c.name as category_name 
            FROM ai_tools t 
            LEFT JOIN categories c ON t.CID = c.CID 
            WHERE 1=1";
    $params = [];

    if ($category) {
        $sql .= " AND c.name = ?";
        $params[] = $category;
    }

    if ($keyword) {
        // 支援多關鍵字拆分搜尋（例如：「免費 新手」）
        $keywords = explode(' ', $keyword);
        foreach ($keywords as $kw) {
            if (trim($kw) === '') continue;
            $sql .= " AND (t.description LIKE ? OR t.name LIKE ? OR c.name LIKE ?)";
            $params[] = "%$kw%";
            $params[] = "%$kw%";
            $params[] = "%$kw%";
        }
    }

    $sql .= " ORDER BY t.tool_ID DESC LIMIT 5";
    
    $stmt = $db->prepare($sql);
    $stmt->execute($params);
    $results = $stmt->fetchAll();

    // 如果還是找不到，至少回傳該分類的熱門工具
    if (empty($results) && $category) {
        $stmt = $db->prepare("SELECT t.tool_ID, t.name, t.description, t.website_url, t.pricing_plans, c.name as category_name FROM ai_tools t LEFT JOIN categories c ON t.CID = c.CID WHERE c.name = ? LIMIT 5");
        $stmt->execute([$category]);
        $results = $stmt->fetchAll();
    }

    return $results;
}

/**
 * 取得個人化設定
 */
function get_personal_context($args) {
    require_once 'jwt_helper.php';
    $db = getDB();
    $token = $args['token'] ?? '';
    
    // 如果沒提供 Token，回傳預設提示
    if (!$token) return ['context' => '無個人化偏好設定'];

    $decoded = JWT::decode($token);
    if (!$decoded || !isset($decoded['uid'])) return ['context' => '登入逾時'];

    $uid = $decoded['uid'];
    
    $stmt = $db->prepare("SELECT system_prompt FROM user WHERE UID = ?");
    $stmt->execute([$uid]);
    $user = $stmt->fetch();
    
    return $user ? ['context' => $user['system_prompt']] : ['context' => '尚未設定個人偏好'];
}

/**
 * 更新個人化設定
 */
function update_personal_context($args) {
    require_once 'jwt_helper.php';
    $db = getDB();
    $token = $args['token'] ?? '';
    $new_context = $args['new_context'] ?? '';
    
    if (!$token) return ['error' => '請先登入'];
    if (!$new_context) return ['error' => '內容不能為空'];

    $decoded = JWT::decode($token);
    if (!$decoded || !isset($decoded['uid'])) return ['error' => '登入逾時'];

    $uid = $decoded['uid'];
    
    $stmt = $db->prepare("UPDATE user SET system_prompt = ? WHERE UID = ?");
    $success = $stmt->execute([$new_context, $uid]);
    
    return $success ? ['status' => 'success', 'message' => '個人偏好已更新'] : ['error' => '更新失敗'];
}

/**
 * 取得社群評價總結
 */
function get_community_insights($args) {
    $db = getDB();
    $tool_name = $args['tool_name'] ?? '';

    $stmt = $db->prepare("SELECT tool_ID FROM ai_tools WHERE name LIKE ?");
    $stmt->execute(["%$tool_name%"]);
    $tool = $stmt->fetch();

    if (!$tool) return ['error' => '找不到該工具'];

    $tid = $tool['tool_ID'];
    
    $sql = "(SELECT stars, comment, '最新' as type FROM tool_reviews WHERE tool_ID = ? ORDER BY comment_time DESC LIMIT 3)
            UNION ALL
            (SELECT stars, comment, '最高' as type FROM tool_reviews WHERE tool_ID = ? ORDER BY stars DESC LIMIT 2)
            UNION ALL
            (SELECT stars, comment, '最低' as type FROM tool_reviews WHERE tool_ID = ? ORDER BY stars ASC LIMIT 2)";
            
    $stmt = $db->prepare($sql);
    $stmt->execute([$tid, $tid, $tid]);
    return $stmt->fetchAll();
}

/**
 * 價格方案分析
 */
function pricing_comparison_expert($args) {
    $db = getDB();
    $tool_names = $args['tool_names'] ?? [];
    if (empty($tool_names)) return ['error' => '請提供工具名稱'];

    $placeholders = str_repeat('?,', count($tool_names) - 1) . '?';
    $sql = "SELECT name, pricing_plans FROM ai_tools WHERE name IN ($placeholders)";
    
    $stmt = $db->prepare($sql);
    $stmt->execute($tool_names);
    $results = $stmt->fetchAll();

    foreach ($results as &$row) {
        $row['pricing_plans'] = json_decode($row['pricing_plans'], true);
    }
    
    return $results;
}

/**
 * 趨勢榜單查詢
 */
function trending_tools_retriever($args) {
    $db = getDB();
    $sql = "SELECT t.name, COUNT(l.tool_ID) as like_count 
            FROM ai_tools t 
            JOIN user_likes l ON t.tool_ID = l.tool_ID 
            GROUP BY t.tool_ID 
            ORDER BY like_count DESC 
            LIMIT 5";
            
    $stmt = $db->query($sql);
    return $stmt->fetchAll();
}
