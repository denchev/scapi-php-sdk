# How to use the SDK

## Login

It will return an array with usid, authentication_code, scope, state and verification_code. You need the verification_code and authentication_code to complete the /token endpoint.

```phpt
$scapiAPI = new \ScapiPHP\Oauth2\Login([
    'base_uri' => '',
    'client_id' => '',
    'channel_id' => '',
    'redirect_uri' => 'http://127.0.0.1:3000/callback'
]);

$customerUsername = ''; // Fill existing customer username
$customerPassword = ''; // Fill existing customer password

$authenticationCode = $scapiAPI->authenticateCustomer($customerUsername, $customerPassword);

print_r($authenticationCode);
```

The _/login_ endpoint needs an active callback uri to call to verify the origin of the request. For development you might just run the in-build PHP server.

```shell
cd server/
php -S localhost:3000
```

