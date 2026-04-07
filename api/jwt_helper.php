<?php
// api/jwt_helper.php — 這是自定義的 JWT (JSON Web Token) 工具類別
// 負責處理 Token 的生成 (Encode) 與驗證解碼 (Decode)

class JWT {
    // 這裡定義了一個「秘密金鑰」(Secret Key)，用來幫 Token 簽名，防止別人偽造
    private static $secret = 'your_super_secret_key_12345'; // 實際上應存放於環境變數，不應直接寫在程式碼中

    /**
     * 生成 JWT Token 的函數
     * $payload 為我們要存儲在 Token 裡的資料 (例如：使用者 ID)
     */
    public static function encode($payload) {
        // 1. 定義 Header (標頭)：告訴別人這是 JWT 格式，且我們使用 HS256 演算法加密
        $header = json_encode(['typ' => 'JWT', 'alg' => 'HS256']);
        
        // 2. 將 Header 和 Payload 進行 Base64Url 編碼 (這是一種能在網址安全傳遞的編碼格式)
        $base64UrlHeader = self::base64UrlEncode($header);
        $base64UrlPayload = self::base64UrlEncode(json_encode($payload));
        
        // 3. 產生簽章 (Signature)：將 Header 與 Payload 串連，並用我們設定的「秘密金鑰」進行 HMAC-SHA256 加密
        $signature = hash_hmac('sha256', $base64UrlHeader . "." . $base64UrlPayload, self::$secret, true);
        // 4. 將簽章也轉成 Base64Url 格式
        $base64UrlSignature = self::base64UrlEncode($signature);
        
        // 5. 將三部分用「點」連接起來，就成了最終的 JWT Token：Header.Payload.Signature
        return $base64UrlHeader . "." . $base64UrlPayload . "." . $base64UrlSignature;
    }

    /**
     * 驗證並解碼 JWT Token 的函數
     * $jwt 為前端傳回來的 Token 字串
     */
    public static function decode($jwt) {
        // 1. 將 Token 按「點」拆分成三個部分
        $tokenParts = explode('.', $jwt);
        // 如果不是三個部分，代表 Token 格式完全錯誤
        if (count($tokenParts) !== 3) return false;

        $header = $tokenParts[0];
        $payload = $tokenParts[1];
        $signatureProvided = $tokenParts[2];

        // 2. 重新計算一次簽章，看看跟 Token 裡帶的是否一樣 (防止 Token 內容被篡改)
        $base64UrlHeader = $header;
        $base64UrlPayload = $payload;
        $signatureCheck = hash_hmac('sha256', $base64UrlHeader . "." . $base64UrlPayload, self::$secret, true);
        $base64UrlSignatureCheck = self::base64UrlEncode($signatureCheck);

        // 如果計算出的簽章與傳來的不同，代表資料可能被動過手腳
        if ($base64UrlSignatureCheck !== $signatureProvided) return false;

        // 3. 驗證成功後，將 Payload 部分解碼回 PHP 陣列
        $payloadDecoded = json_decode(self::base64UrlDecode($payload), true);

        // 4. 檢查 Token 是否已經過期 (exp 欄位)
        if (isset($payloadDecoded['exp']) && $payloadDecoded['exp'] < time()) return false;

        // 全部驗證通過，回傳儲存在內的資料
        return $payloadDecoded;
    }

    // 將資料轉為網址安全的 Base64 格式
    private static function base64UrlEncode($data) {
        return str_replace(['+', '/', '='], ['-', '_', ''], base64_encode($data));
    }

    // 將網址安全的 Base64 格式轉回原始資料
    private static function base64UrlDecode($data) {
        return base64_decode(str_replace(['-', '_'], ['+', '/'], $data));
    }
}
