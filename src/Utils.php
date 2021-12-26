<?php

declare(strict_types=1);

namespace ScapiPHP;

class Utils
{
    /**
     * @param string $base64EncodedString
     * @return string
     */
    static function base64_url(string $base64EncodedString): string
    {
        $str = str_replace('=', '', $base64EncodedString);
        $str = str_replace('+', '-', $str);
        return str_replace('/', '_', $str);
    }

    /**
     * Source: https://stackoverflow.com/questions/4356289/php-random-string-generator
     * @param int $length
     * @return string
     */
    static function randomString(int $length = 10): string
    {
        $characters = 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789';
        $charactersLength = strlen($characters);
        $randomString = '';
        for ($i = 0; $i < $length; $i++) {
            $randomString .= $characters[rand(0, $charactersLength - 1)];
        }
        return $randomString;
    }
}