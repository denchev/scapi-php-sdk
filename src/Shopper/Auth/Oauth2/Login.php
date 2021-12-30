<?php
namespace ScapiPHP\Shopper\Auth\Oauth2;

use Exception;
use GuzzleHttp\Exception\GuzzleException;
use ScapiPHP\Clients\ClientInterface;
use ScapiPHP\Exceptions\ServiceCallbackErrorException;
use ScapiPHP\Exceptions\ServiceConnectionException;
use ScapiPHP\Exceptions\ServiceResponseException;
use ScapiPHP\Exceptions\ServiceResponseInvalidException;
use ScapiPHP\ScapiClient;
use ScapiPHP\Utils;

class Login extends ScapiClient
{
    const CODE_VERIFIER_LENGTH = 128;

    /**
     * @return array
     */
    private function codeChallenge(): array
    {
        // Code verifier is just a random string
        $codeVerifier = Utils::randomString(self::CODE_VERIFIER_LENGTH);

        $hashed = hash('sha256', $codeVerifier, true);
        $asBase64 = base64_encode($hashed);

        return [
            'code_challenge' => Utils::base64_url($asBase64),
            'code_verifier' => $codeVerifier
        ];
    }

    /**
     * @param string $username
     * @param string $password
     * @return array|null
     * @throws Exception
     */
    public function authenticateCustomer(string $username, string $password): ?array
    {
        try {
            ['code_challenge' => $codeChallenge, 'code_verifier' => $codeVerifier] = $this->codeChallenge();
            $basicAuth = base64_encode($username . ':' . $password);

            /** \Psr\Http\Message\ResponseInterface $response */
            $response = $this->client->request('POST', 'oauth2/login', [
                'headers' => [
                    'Authorization' => 'Basic ' . $basicAuth,
                    'Content-Type' => 'application/x-www-form-urlencoded'
                ],
                'body' => http_build_query([
                    'code_challenge' => $codeChallenge,
                    'channel_id' => $this->options['channel_id'],
                    'redirect_uri' => $this->options['redirect_uri'],
                    'client_id' => $this->options['client_id']
                ])
            ]);

            if ($response->getStatusCode() === 200) {
                $body = $response->getBody()->getContents();
                $contentAsJson = json_decode($body);

                if ($contentAsJson === null) {
                    throw new ServiceResponseInvalidException("Service response is in an invalid format.");
                }

                $authenticationCode = $contentAsJson->code;
                $usid = $contentAsJson->usid;
                $state = $contentAsJson->state;
                $scope = $contentAsJson->scope;

                return [
                    'authentication_code' => $authenticationCode,
                    'code_verifier' => $codeVerifier,
                    'usid' => $usid,
                    'state' => $state,
                    'scope' => $scope
                ];
            } else {
                throw new ServiceResponseException("Services response status is an error.");
            }

        } catch (GuzzleException $e) {
            if (strstr($e->getMessage(), $this->options['redirect_uri']) >= 0) {
                throw new ServiceCallbackErrorException("Salesforce cannot connect to your callback. Check redirect_uri service.");
            } else {
                throw new ServiceConnectionException("Service connection cannot be established. Message: " . $e->getMessage());
            }
        }

        return null;
    }
}
