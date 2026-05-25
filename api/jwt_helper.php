<?php
// api/jwt_helper.php — 自定義 JWT 工具類別，負責 Token 的編碼與解碼

require_once __DIR__ . '/env_loader.php';

class JWT {
    private static $secret = null;

    private static function getSecret() {
        if (self::$secret === null) {
            $secret = getenv('JWT_SECRET');
            if (!$secret || strlen($secret) < 32) {
                error_log('JWT_SECRET 未設定或長度不足 32 字元');
                throw new RuntimeException('JWT 設定錯誤');
            }
            self::$secret = $secret;
        }
        return self::$secret;
    }

    public static function encode($payload) {
        $header = json_encode(['typ' => 'JWT', 'alg' => 'HS256']);
        $base64UrlHeader = self::base64UrlEncode($header);
        $base64UrlPayload = self::base64UrlEncode(json_encode($payload));

        $signature = hash_hmac('sha256', $base64UrlHeader . "." . $base64UrlPayload, self::getSecret(), true);
        $base64UrlSignature = self::base64UrlEncode($signature);

        return $base64UrlHeader . "." . $base64UrlPayload . "." . $base64UrlSignature;
    }

    public static function decode($jwt) {
        if (!is_string($jwt) || $jwt === '') return false;

        $tokenParts = explode('.', $jwt);
        if (count($tokenParts) !== 3) return false;

        $header = $tokenParts[0];
        $payload = $tokenParts[1];
        $signatureProvided = $tokenParts[2];

        $signatureCheck = hash_hmac('sha256', $header . "." . $payload, self::getSecret(), true);
        $base64UrlSignatureCheck = self::base64UrlEncode($signatureCheck);

        // 用 hash_equals 做 timing-safe 比對，避免 timing attack
        if (!hash_equals($base64UrlSignatureCheck, $signatureProvided)) return false;

        $payloadDecoded = json_decode(self::base64UrlDecode($payload), true);
        if (!is_array($payloadDecoded)) return false;

        if (isset($payloadDecoded['exp']) && $payloadDecoded['exp'] < time()) return false;

        return $payloadDecoded;
    }

    private static function base64UrlEncode($data) {
        return str_replace(['+', '/', '='], ['-', '_', ''], base64_encode($data));
    }

    private static function base64UrlDecode($data) {
        return base64_decode(str_replace(['-', '_'], ['+', '/'], $data));
    }
}
