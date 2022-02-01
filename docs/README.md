# How to use the SDK

You need to know your instance short_code and org_id, the client_id, client_secret, channel_id

### General options

```phpt
$options = [
        'short_code' => '',
        'organization_id' => '',
        'client_id' => '',
        'client_secret' => '',
        'channel_id' => '',
        'redirect_uri' => 'http://127.0.0.1:3000/callback'
    ];
```

## Login

It will return an array with usid, authentication_code, scope, state and verification_code. You need the verification_code and authentication_code to complete the /token endpoint.

```phpt
$scapiAPILogin = new \ScapiPHP\Shopper\Auth\Oauth2\Login($options);

$customerUsername = ''; // Fill existing customer username
$customerPassword = ''; // Fill existing customer password

$authenticationCode = $scapiAPILogin->authenticateCustomer($customerUsername, $customerPassword);

print_r($authenticationCode);
```

The _/login_ endpoint needs an active callback uri to call to verify the origin of the request. For development you might just run the in-build PHP server.

```shell
cd server/
php -S localhost:3000
```

## Token

After successful execution of the Login endpoint, you can continue to the next and final step - getting the access token. 

```phpt
$scapiAPIToken = new \ScapiPHP\Shopper\Auth\Oauth2\Token($options);
$tokens = $scapiAPIToken->getAccessToken($authenticationCode['authentication_code'], $authenticationCode['code_verifier']);

$accessToken = $tokens->access_token;
```

Now you can use the $accessToken variable as Bearer token for the rest of the APIs access.

## Logout

If you would like to logout customer i.e. end authorization you can use the Shopper\Auth\Oauth2\Logout class

```phpt
$logout = $scapiAPILogout->authenticate($YOUR_ACCESS_TOKEN)->logoutCustomer($YOUR_REFRESH_TOKEN, $channelId);
```

## Revoke

```phpt
$scapiAPIRevoke = new \ScapiPHP\Shopper\Auth\Oauth2\Revoke($options);

$result = $scapiAPIRevoke->revokeToken($tokens->refresh_token);
```