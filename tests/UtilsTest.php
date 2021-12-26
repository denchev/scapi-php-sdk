<?php

declare(strict_types=1);

namespace ScapiPHP\Test;

use PHPUnit\Framework\TestCase;
use ScapiPHP\Utils;

class UtilsTest extends TestCase
{
    public function testRandomString()
    {
        $length = 20;
        $randomString = Utils::randomString($length);
        $this->assertEquals($length, strlen($randomString));
    }

    public function testBase64_url()
    {
        $testString = 'this is test + and it should not contain either / or =';
        $base64url = Utils::base64_url($testString);
        $this->assertEquals('this is test - and it should not contain either _ or ', $base64url);
    }
}