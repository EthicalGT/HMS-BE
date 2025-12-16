<?php

function generate_otp(int $length): string
    {
        $chars = 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789';
        $otp = '';

        for ($i = 0; $i < $length; $i++) {
            $otp .= $chars[random_int(0, strlen($chars) - 1)];
        }

        return $otp;
    }
    
?>