<?php

namespace ScapiPHP\Exceptions;

use JetBrains\PhpStorm\Internal\LanguageLevelTypeAware;

class ServiceConnectionException extends \Exception
{
    public function __construct(string $message = "", int $code = 0, ?Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }
}