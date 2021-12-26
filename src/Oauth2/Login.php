<?php
namespace ScapiPHP\Oauth2;

use Exception;
use GuzzleHttp\Exception\GuzzleException;
use JetBrains\PhpStorm\ArrayShape;
use ScapiPHP\ScapiClient;

class Login extends ScapiClient
{
    const CODE_VERIFIER_LENGTH = 43;
    /**
     * Source: https://stackoverflow.com/questions/4356289/php-random-string-generator
     * @param int $length
     * @return string
     */
    private function randomString(int $length = 10): string
    {
        $characters = 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789';
        $charactersLength = strlen($characters);
        $randomString = '';
        for ($i = 0; $i < $length; $i++) {
            $randomString .= $characters[rand(0, $charactersLength - 1)];
        }
        return $randomString;
    }

    /**
     * @param string $base64EncodedString
     * @return string
     */
    private function base64url(string $base64EncodedString): string
    {
        $str = str_replace('=', '', $base64EncodedString);
        $str = str_replace('+', '-', $str);
        return str_replace('/', '_', $str);
    }

    /**
     * @return array
     */
    #[ArrayShape(['code_challange' => "string", 'code_verifier' => "string"])] private function codeChallenge(): array
    {
        // Code verifier is just a random string
        $codeVerifier = $this->randomString(self::CODE_VERIFIER_LENGTH);

        $hashed = hash('sha256', $codeVerifier, true);
        $asBase64 = base64_encode($hashed);

        return [
            'code_challenge' => $this->base64url($asBase64),
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
                    throw new Exception("Invalid response from Salesforce instance.");
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
