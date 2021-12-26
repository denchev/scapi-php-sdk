<?php
namespace ScapiPHP\Oauth2;

use GuzzleHttp\Exception\GuzzleException;
use JetBrains\PhpStorm\ArrayShape;
use ScapiPHP\ScapiClient;

class Login extends ScapiClient
{
    /**
     * Source: https://stackoverflow.com/questions/4356289/php-random-string-generator
     * @param int $length
     * @return string
     */
    private function randomString(int $length = 10): string
    {
        $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $charactersLength = strlen($characters);
        $randomString = '';
        for ($i = 0; $i < $length; $i++) {
            $randomString .= $characters[rand(0, $charactersLength - 1)];
        }
        return $randomString;
    }

    /**
     * @return array
     */
    #[ArrayShape(['code_challange' => "array|string|string[]", 'code_verifier' => "string"])] private function codeChallenge(): array
    {
        // Random string
        $randomString = $this->randomString(15);

        $hashed = hash('sha256', $randomString);
        $asBase64 = base64_encode($hashed);

        return [
            'code_challenge' => str_replace(['=', '+', '\\'], '', $asBase64),
            'code_verifier' => $randomString
        ];
    }

    /**
     * @param string $username
     * @param string $password
     * @return array|null
     * @throws \Exception
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
                    throw new \Exception("Invalid response from Salesforce instance.");
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
            }

        } catch (GuzzleException $e) {
            $this->logger->critical($e->getMessage());
        }

        return null;
    }
}
