<?php
namespace ScapiPHP\Shopper\Auth\Oauth2;

use GuzzleHttp\Exception\GuzzleException;
use ScapiPHP\Exceptions\ServiceResponseInvalidException;
use ScapiPHP\ScapiClient;

class Revoke extends ScapiClient
{
    /**
     * @throws ServiceResponseInvalidException
     */
    public function revokeToken($refreshToken)
    {
        try {
            $basicAuth = base64_encode($this->options['client_id'] . ':' . $this->options['client_secret']);

            $response = $this->client->request('POST', 'oauth2/revoke', [
                'headers' => [
                    'Authorization' => 'Basic ' . $basicAuth,
                    'Content-Type' => 'application/x-www-form-urlencoded'
                ],
                'body' => http_build_query([
                    'token' => $refreshToken,
                    'token_type_hint' => 'REFRESH_TOKEN',
                ])
            ]);

            if ($response->getStatusCode() === 200) {
                $body = $response->getBody()->getContents();

                $contentAsJson = json_decode($body);

                if ($contentAsJson === null) {
                    if ($body === 'Success') {
                        return true;
                    } else {
                        throw new ServiceResponseInvalidException("Service response is in an invalid format.");
                    }
                }

                return $contentAsJson;
            }
        } catch (GuzzleException $ex) {

        }
    }
}