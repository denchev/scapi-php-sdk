<?php
namespace ScapiPHP\Shopper\Auth\Oauth2;

use GuzzleHttp\Exception\GuzzleException;
use ScapiPHP\ScapiClient;

class Logout extends ScapiClient
{
    public function logoutCustomer(string $refreshToken, string $channelId = null)
    {
        try {
            $data = [];
            $data['client_id'] = $this->options['client_id'];
            $data['refresh_token'] = $refreshToken;

            // Channel id is not always required
            if ($channelId != null) {
                $data['channel_id'] = $channelId;
            }

            $query = http_build_query($data);

            $response = $this->client->request("GET", "oauth2/logout?" . $query, [
                'headers' => [
                    'Authorization' => 'Bearer ' . $this->getBearerToken()
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