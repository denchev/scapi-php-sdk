<?php

namespace ScapiPHP\Oauth2;

use GuzzleHttp\Exception\GuzzleException;
use ScapiPHP\ScapiClient;

class Token extends ScapiClient
{
    public function getAccessToken(string $code, string $codeVerifier)
    {
        $grantType = 'authorization_code_pkce';
        $data = [];
        $data['grant_type'] = $grantType;
        $data['redirect_uri'] = $this->options['redirect_uri'];
        $data['code'] = $code;
        $data['channel_id'] = $this->options['channel_id'];
        $data['code_verifier'] = $codeVerifier;

        $query = http_build_query($data);

        $basicAuth = base64_encode($this->options['client_id'] . ':' . $this->options['client_secret']);

        try {
            $response = $this->client->request('POST', 'oauth2/token?' . $query, [
                'headers' => [
                    'Authorization' => 'Basic ' . $basicAuth,
                    'Content-Type' => 'application/x-www-form-urlencoded'
                ]
            ]);
            if ($response->getStatusCode() === 200) {
                $body = $response->getBody()->getContents();
                $contentAsJson = json_decode($body);

                if ($contentAsJson !== null) {
                    return $contentAsJson;
                }
            }
        } catch (GuzzleException $ex) {
            echo $ex->getMessage();
        }
    }
}