<?php

namespace SensioLabs\Melody\Resource;

use SensioLabs\Melody\Exception\InvalidCredentialsException;
use SensioLabs\Melody\Security\Token;

/**
 * @author Maxime STEINHAUSSER <maxime.steinhausser@gmail.com>
 */
interface AuthenticableResourceInterface
{
    const CREDENTIALS_NORMAL = 'normal';
    const CREDENTIALS_SECRET = 'secret';

    /**
     * Returns an array of the required credentials.
     *
     * The array should be like:
     *
     * array(
     *     'username' => AuthenticableResourceInterface::CREDENTIALS_NORMAL,
     *     'password' => AuthenticableResourceInterface::CREDENTIALS_SECRET,
     * );
     *
     * or array('token') if none of the information are particularly sensitive (defaults to AuthenticableResourceInterface::CREDENTIALS_NORMAL).
     *
     * @return array
     */
    public function getRequiredCredentials();

    /**
     * Authenticates using given credentials and returns a security token for further use.
     *
     * @param array $credentials
     *
     * @return Token
     *
     * @throws InvalidCredentialsException
     */
    public function authenticate(array $credentials);

    /**
     * Get the key allowing to identify this resource.
     *
     * @return string
     */
    public function getKey();
}
