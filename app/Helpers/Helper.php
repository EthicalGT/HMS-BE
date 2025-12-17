<?php

use Firebase\JWT\JWT;
use Firebase\JWT\Key;

if (!function_exists('generate_otp')) {
    /**
     * Generate random OTP of given length
     */
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




// if we want to use jwt then first    
//composer require firebase/php-jwt
if (!function_exists('create_jwt')) {
    /**
     * Generate JWT token
     *
     * @param array $payload
     * @param int $expiryMinutes
     * @return string
     */
    function create_jwt(array $payload, int $expiryMinutes = 60): string
    {
         $key = config('jwt.secret');


        if (!$key) {
            throw new \Exception('JWT_SECRET is not set in .env file');
        }

        $issuedAt = time();
        $expire = $issuedAt + ($expiryMinutes * 60);

        $tokenPayload = array_merge($payload, [
            'iat' => $issuedAt,
            'exp' => $expire,
        ]);

        return JWT::encode($tokenPayload, $key, 'HS256');
    }
}
