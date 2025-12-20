<?php

use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Cache;

if (!function_exists('generate_otp')) {
    function generate_otp(int $length): string
    {
        $chars = 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789';
        $otp = '';

        for ($i = 0; $i < $length; $i++) {
            $otp .= $chars[random_int(0, strlen($chars) - 1)];
        }

        return $otp;
    }
}

const JWT_BLACKLIST_PREFIX = 'jwt_blacklist_';

// Create JWT Token
function create_jwt(array $payload, int $expiryMinutes = 10): string
{
    $key = config('jwt.secret');

    if (!$key) {
        throw new \Exception('JWT_SECRET is not set');
        echo "JWT_SECRET is not set in the environment variables.";
        exit;
    }

    $issuedAt = time();
    $expire = $issuedAt + ($expiryMinutes * 60);

    return JWT::encode(array_merge($payload, [
        'iat' => $issuedAt,
        'exp' => $expire,
        'jti' => (string) Str::uuid(),
    ]), $key, 'HS256');
}

// Validate JWT Token 
function is_jwt_valid(string $token): bool
{
    try {
        $key = config('jwt.secret');
        $decoded = JWT::decode($token, new Key($key, 'HS256'));

        if ($decoded->exp < time()) {
            return false;
        }

        return !Cache::has(JWT_BLACKLIST_PREFIX . $decoded->jti);
    } catch (\Exception $e) {
        return false;
    }
}

// Destroy JWT Token (LOGOUT)
function destroy_jwt(string $token): void
{
    try {
        $key = config('jwt.secret');
        $decoded = JWT::decode($token, new Key($key, 'HS256'));

        $ttl = $decoded->exp - time();

        if ($ttl > 0) {
            Cache::put(
                JWT_BLACKLIST_PREFIX . $decoded->jti,
                true,
                $ttl
            );
        }
    } catch (\Exception $e) {
        echo "Ignored invalid tokens";
    }
}
