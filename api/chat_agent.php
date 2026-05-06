<?php
header('Content-Type: application/json');
require_once 'config.php';
require_once 'tools_handler.php';

$input = json_decode(file_get_contents('php://input'), true);
$user_messages = $input['messages'] ?? [];

if (empty($user_messages)) {
    echo json_encode(['error' => '請提供訊息內容']);
    exit;
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
            "name" => "get_personal_context",
            "description" => "讀取使用者的職業背景與偏好以提供精準推薦。"
        ]
    ],
    [
        "type" => "function",
        "function" => [
            "name" => "update_personal_context",
            "description" => "更新使用者的個人偏好、職業背景或對話風格設定。",
            "parameters" => [
                "type" => "object",
                "properties" => [
                    "new_context" => [
                        "type" => "string",
                        "description" => "完整的偏好描述，例如：'我是一名學生，偏好免費工具，請用親切的語氣回答'。"
                    ]
                ],
                "required" => ["new_context"]
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
        "content" => "你是一個專業的 AI 工具顧問。
請務必遵守以下排版規則：
1. 使用繁體中文回答。
2. 使用 Markdown 格式：標題用 ##，重點用 **粗體**，列表用 - 或 1. 確保段落之間有足夠空行。
3. **重要**：當你推薦任何工具時，必須在工具名稱旁邊加上跳轉連結，格式為：[查看詳情](/tool/工具ID)。工具ID可以從資料庫回傳的數據中獲取。
4. 你可以調用 get_personal_context 來了解使用者偏好。當使用者告知新的偏好時，請主動調用 update_personal_context 來儲存。
5. 當使用者詢問工具時，請優先調用工具查詢資料庫，不要瞎猜。"
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
            // 如果是抓取或更新個人設定，自動注入 token
            if ($function_name === 'get_personal_context' || $function_name === 'update_personal_context') {
                $arguments['token'] = $input['token'] ?? '';
            }
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
