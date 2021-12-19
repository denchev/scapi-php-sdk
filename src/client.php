<?php
namespace ScapiPHP;

use Symfony\Contracts\HttpClient\HttpClientInterface;

class ScapiClient
{

    public function __construct(private readonly HttpClientInterface $client) {}
}