<?php

namespace SensioLabs\Melody\Resource;

use SensioLabs\Melody\Exception\InvalidCredentialsException;
use SensioLabs\Melody\Security\SafeToken;

class GistResource extends Resource implements AuthenticableResourceInterface
{
    /**
     * {@inheritdoc}
     */
    public function getRequiredCredentials()
    {
        return array(
            'username' => self::CREDENTIALS_NORMAL,
            'password' => self::CREDENTIALS_SECRET,
        );
    }

    /**
     * {@inheritdoc}
     */
    public function authenticate(array $credentials)
    {
        if (empty($credentials['username']) || empty($credentials['password'])) {
            throw new InvalidCredentialsException('You should provide non-empty "username" and "password" information.');
        }

        $handle = curl_init();

        $payload = json_encode(array(
            'scopes' => array('public_repo'),
            'note' => sprintf('Melody on %s %s', gethostname(), date('Y-m-d Hi')),
        ));

        curl_setopt_array($handle, array(
            CURLOPT_URL => 'https://api.github.com/authorizations',
            CURLOPT_CUSTOMREQUEST => 'POST',
            CURLOPT_POSTFIELDS => $payload,
            CURLOPT_HTTPHEADER => array(
                'Accept: application/vnd.github.v3+json',
                'User-Agent: Melody-Script',
                'Content-Type: application/json',
                'Content-Length: '.strlen($payload),
            ),
            CURLOPT_RETURNTRANSFER => 1,
            CURLOPT_USERPWD => sprintf('%s:%s', $credentials['username'], $credentials['password']),
        ));

        if ($http_proxy = filter_input(INPUT_ENV, 'HTTPS_PROXY', FILTER_SANITIZE_URL)) {
            curl_setopt($handle, CURLOPT_PROXY, $http_proxy);
        }

        $content = curl_exec($handle);
        curl_close($handle);

        $response = json_decode($content, true);

        if (!isset($response['token'])) {
            throw new InvalidCredentialsException(isset($response['message']) ? $response['message'] : 'Unable to get token.');
        }

        return new SafeToken(array('oauth_token' => $response['token']));
    }

    /**
     * {@inheritdoc}
     */
    public function getKey()
    {
        return 'gist';
    }
}
