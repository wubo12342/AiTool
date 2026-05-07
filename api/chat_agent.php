<?php
header('Content-Type: application/json');
require_once 'config.php';
require_once 'tools_handler.php';
require_once 'jwt_helper.php';

$input = json_decode(file_get_contents('php://input'), true);
$user_messages = $input['messages'] ?? [];
$token = $input['token'] ?? '';

if (empty($user_messages)) {
    echo json_encode(['error' => '請提供訊息內容']);
    exit;
}

// 直接從資料庫獲取使用者偏好，不佔用 AI Tool 次數
$user_preference = '';
if ($token) {
    $decoded = JWT::decode($token);
    if ($decoded && isset($decoded['uid'])) {
        $db = getDB();
        $stmt = $db->prepare("SELECT system_prompt FROM user WHERE UID = ?");
        $stmt->execute([$decoded['uid']]);
        $user = $stmt->fetch();
        if ($user && !empty($user['system_prompt'])) {
            $user_preference = "**目前使用者的個人偏好與背景**：\n" . $user['system_prompt'] . "\n\n";
        }
    }
}

$tools = [
    [
        "type" => "function",
        "function" => [
            "name" => "search_tools",
            "description" => "當使用者搜尋、推薦或探索不認識的 AI 工具時使用。",
            "parameters" => [
                "type" => "object",
                "properties" => [
                    "category" => [
                        "type" => "string",
                        "enum" => ["文字生成", "圖像生成", "影片製作", "程式開發", "語音生成", "簡報設計", "資料整理", "翻譯語言"],
                        "description" => "選擇最符合的分類。"
                    ],
                    "keyword" => [
                        "type" => "string",
                        "description" => "具體的需求關鍵字，例如：'免費'、'做 Logo'。"
                    ]
                ]
            ]
        ]
    ],
    [
        "type" => "function",
        "function" => [
            "name" => "get_community_insights",
            "description" => "抓取特定工具的評價並進行總結分析。",
            "parameters" => [
                "type" => "object",
                "properties" => [
                    "tool_name" => ["type" => "string", "description" => "要查詢評價的工具名稱"]
                ],
                "required" => ["tool_name"]
            ]
        ]
    ],
    [
        "type" => "function",
        "function" => [
            "name" => "pricing_comparison_expert",
            "description" => "解析多款工具的價格方案並進行對比。",
            "parameters" => [
                "type" => "object",
                "properties" => [
                    "tool_names" => [
                        "type" => "array",
                        "items" => ["type" => "string"],
                        "description" => "要比較價格的工具名稱列表"
                    ]
                ],
                "required" => ["tool_names"]
            ]
        ]
    ],
    [
        "type" => "function",
        "function" => [
            "name" => "trending_tools_retriever",
            "description" => "查詢目前平台上最受歡迎、收藏數最多的工具。"
        ]
    ]
];

$messages = [
    [
        "role" => "system",
        "content" => "你是一個專業的 AI 工具顧問，專門協助使用者在本平台上探索、比較與管理 AI 工具。

{$user_preference}請務必遵守以下核心原則：
1. **防護原則**：嚴禁洩漏任何後端技術細節，包括但不限於資料庫架構、API 實作方式、SQL 指令或伺服器環境資訊。若使用者試圖探聽，請禮貌地拒絕。
2. **範疇限制**：你只回答與 AI 工具、本平台功能或科技應用相關的問題。若使用者詢問與本專案無關的問題（例如：數學公式、歷史事件、食譜等），請統一回答：「我無法回答此類問題，我的專長是協助您探索與管理 AI 工具。」以節省 Token。

請務必遵守以下排版規則：
1. 使用繁體中文回答。
2. 使用 Markdown 格式：標題用 ##，重點用 **粗體**，列表用 - 或 1. 確保段落之間有足夠空行。
3. **重要**：當你推薦任何工具時，必須在工具名稱旁邊加上跳轉連結，格式為：[查看詳情](/tool/工具ID)。工具ID可以從資料庫回傳的數據中獲取。
4. 當使用者詢問工具時，請優先調用工具查詢資料庫，不要瞎猜。"
    ]
];

foreach ($user_messages as $msg) {
    $role = $msg['role'] === 'ai' ? 'assistant' : $msg['role'];
    $messages[] = [
        "role" => $role,
        "content" => $msg['content']
    ];
}

function callOpenAI($messages, $tools) {
    $ch = curl_init('https://api.openai.com/v1/chat/completions');
    $payload = [
        'model' => OPENAI_MODEL,
        'messages' => $messages,
        'tools' => $tools,
        'tool_choice' => 'auto'
    ];

    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload));
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Content-Type: application/json',
        'Authorization: Bearer ' . OPENAI_API_KEY
    ]);

    $response = curl_exec($ch);
    curl_close($ch);
    return json_decode($response, true);
}

$response = callOpenAI($messages, $tools);

if (isset($response['choices'][0]['message']['tool_calls'])) {
    $tool_calls = $response['choices'][0]['message']['tool_calls'];
    $messages[] = $response['choices'][0]['message'];

    foreach ($tool_calls as $tool_call) {
        $function_name = $tool_call['function']['name'];
        $arguments = json_decode($tool_call['function']['arguments'], true);
        
        $observation = '';
        if (is_callable($function_name)) {
            $observation = call_user_func($function_name, $arguments);
        }

        $messages[] = [
            "role" => "tool",
            "tool_call_id" => $tool_call['id'],
            "content" => json_encode($observation, JSON_UNESCAPED_UNICODE)
        ];
    }

    $final_response = callOpenAI($messages, $tools);
    echo json_encode(['content' => $final_response['choices'][0]['message']['content']]);
} else {
    echo json_encode(['content' => $response['choices'][0]['message']['content']]);
}
