<?php

namespace SensioLabs\Melody\Resource;

/**
 * @author Maxime STEINHAUSSER <maxime.steinhausser@gmail.com>
 */
interface AuthenticableResourceInterface
{
    /**
     * Get the key allowing to identify this resource.
     *
     * @return string
     */
    public function getKey();
}
