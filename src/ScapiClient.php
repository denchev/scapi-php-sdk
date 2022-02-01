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

    protected $requestHeaders = [];

    public function __construct(array $options = array()) {

        $default = ['version' => 'v1', 'short_code' => '', 'organization_id' => ''];
        $options = array_merge($default, $options);

        $options['base_uri'] = str_replace(
            ['{short_code}', '{organization_id}', '{version}'],
            [$options['short_code'], $options['organization_id'], $options['version']],
            'https://{short_code}.api.commercecloud.salesforce.com/shopper/auth/{version}/organizations/{organization_id}/'
        );

        $this->options = $options;

        $this->client = new Client([
            'base_uri' => $this->options['base_uri'],
            'timeout' => 2.0
        ]);
    }

    protected function headers() {

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

    public function get($endpoint)
    {

    }
}