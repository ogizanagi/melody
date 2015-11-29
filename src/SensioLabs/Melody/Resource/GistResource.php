<?php

namespace SensioLabs\Melody\Resource;

class GistResource extends Resource implements AuthenticableResourceInterface
{
    /**
     * {@inheritdoc}
     */
    public function getKey()
    {
        return 'gist';
    }
}
