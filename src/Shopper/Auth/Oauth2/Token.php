<?php

namespace ScapiPHP\Shopper\Auth\Oauth2;

use GuzzleHttp\Exception\GuzzleException;
use ScapiPHP\Exceptions\ServiceConnectionException;
use ScapiPHP\Exceptions\ServiceResponseInvalidException;
use ScapiPHP\ScapiClient;

class Token extends ScapiClient
{
    /**
     * @param string $code
     * @param string $codeVerifier
     * @param string|null $usid
     * @return mixed|void
     * @throws ServiceConnectionException
     */
    public function getAccessToken(string $code, string $codeVerifier, string $usid = null)
    {
        $grantType = 'authorization_code_pkce';
        $data = [];
        $data['grant_type'] = $grantType;
        $data['redirect_uri'] = $this->options['redirect_uri'];
        $data['code'] = $code;
        $data['channel_id'] = $this->options['channel_id'];
        $data['code_verifier'] = $codeVerifier;

        // usid is optional
        if ($usid) {
            $data['usid'] = $usid;
        }

        $query = http_build_query($data);

        $basicAuth = base64_encode($this->options['client_id'] . ':' . $this->options['client_secret']);

        try {
            $response = $this->client->request('POST', 'oauth2/token?' . $query, [
                'headers' => [
                    'Authorization' => 'Basic ' . $basicAuth,
                    'Content-Type' => 'application/x-www-form-urlencoded'
                ]
            ]);
            return $this->handleResponse($response);
        } catch (GuzzleException $ex) {
            throw new ServiceConnectionException("Service connection cannot be established.");
        }

        return null;
    }

    /**
     * @throws ServiceResponseInvalidException
     * @throws ServiceConnectionException
     */
    public function getAccessTokenByRefreshToken(string $refreshToken)
    {
        $grantType = 'refresh_token';
        $data = [];
        $data['grant_type'] = $grantType;
        $data['redirect_uri'] = $this->options['redirect_uri'];
        $data['channel_id'] = $this->options['channel_id'];

        $query = http_build_query($data);

        $basicAuth = base64_encode($this->options['client_id'] . ':' . $this->options['client_secret']);

        try {
            $response = $this->client->request('POST', 'oauth2/token?' . $query, [
                'headers' => [
                    'Authorization' => 'Basic ' . $basicAuth,
                    'Content-Type' => 'application/x-www-form-urlencoded'
                ],
                'body' => 'refresh_token=' . $refreshToken
            ]);
            return $this->handleResponse($response);
        } catch (GuzzleException $ex) {
            throw new ServiceConnectionException("Service connection cannot be established.");
        }

        return null;
    }

    /**
     * @throws ServiceResponseInvalidException
     */
    private function handleResponse(\Psr\Http\Message\ResponseInterface $response)
    {
        if ($response->getStatusCode() === 200) {
            $body = $response->getBody()->getContents();
            $contentAsJson = json_decode($body);

            if ($contentAsJson !== null) {
                return $contentAsJson;
            }
        } else {
            throw new ServiceResponseInvalidException();
        }
    }
}