<?php

namespace SensioLabs\Melody\Exception;

use SensioLabs\Melody\Resource\AuthenticableResourceInterface;

/**
 * @author Maxime STEINHAUSSER <maxime.steinhausser@gmail.com>
 */
class AuthenticationRequiredException extends \LogicException
{
    private $resource;

    public function __construct(AuthenticableResourceInterface $resource, $message, $code = 0, \Exception $previous = null)
    {
        parent::__construct($message, $code, $previous);
        $this->resource = $resource;
    }

    /**
     * @return AuthenticableResourceInterface
     */
    public function getResource()
    {
        return $this->resource;
    }
}
