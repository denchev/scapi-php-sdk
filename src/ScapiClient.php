<?php
namespace ScapiPHP;

use GuzzleHttp\Client;
use Monolog\Logger;

class ScapiClient
{
    protected Client $client;

    protected Logger $logger;

    protected array $options = [];

    protected string $accessToken;

    public function __construct(array $options = array()) {
        $this->options = $options;

        $this->client = new Client([
            'base_uri' => $options['base_uri'],
            'timeout' => 2.0
        ]);
        $this->logger = new Logger('scapi-api');
    }

    public function authenticate($accessToken): static
    {
        $this->accessToken = $accessToken;

        return $this;
    }

    protected function getBearerToken(): string
    {
        return $this->accessToken;
    }
}