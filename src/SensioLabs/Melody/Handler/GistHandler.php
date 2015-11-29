<?php

namespace SensioLabs\Melody\Handler;

use SensioLabs\Melody\Composer\Composer;
use SensioLabs\Melody\Exception\AuthenticationRequiredException;
use SensioLabs\Melody\Handler\Github\Gist;
use SensioLabs\Melody\Resource\AuthenticableResourceInterface;
use SensioLabs\Melody\Resource\GistResource;
use SensioLabs\Melody\Resource\Metadata;
use SensioLabs\Melody\Security\Token;
use SensioLabs\Melody\Security\TokenStorage;

/**
 * Class GistHandler.
 *
 * @author Charles Sarrazin <charles@sarraz.in>
 * @author Grégoire Pineau <lyrixx@lyrixx.info>
 * @author Jérémy Derussé <jeremy@derusse.com>
 */
class GistHandler implements ResourceHandlerInterface
{
    private $authenticationStorage;

    public function __construct(TokenStorage $authenticationStorage)
    {
        $this->authenticationStorage = $authenticationStorage;
    }

    /**
     * {@inheritdoc}
     */
    public function supports($uri)
    {
        return 0 !== preg_match(Gist::URI_PATTERN, $uri);
    }

    /**
     * {@inheritdoc}
     */
    public function createResource($filename)
    {
        $resource = new GistResource('');
        $gist = new Gist($filename, $this->getOAuthToken($resource));
        $data = $gist->get();
        $content = $data['content'];
        $status = $data['status'];

        if (200 !== $status) {
            if (in_array($status, array(401, 403))) {
                throw new AuthenticationRequiredException($resource, $content['message']);
            }

            $message = 'There is an issue with your gist URL: ';
            if (array_key_exists('message', $content)) {
                throw new \InvalidArgumentException($message.$content['message']);
            }

            throw new \InvalidArgumentException($message.'Expected 200 status, got '.$status);
        }

        $files = $content['files'];

        // Throw an error if the gist contains multiple files
        if (1 !== count($files)) {
            throw new \InvalidArgumentException('The gist should contain a single file');
        }

        // Fetch the only element in the array
        $file = current($files);
        $metadata = new Metadata(
            $content['id'],
            $content['owner']['login'],
            new \DateTime($content['created_at']),
            new \DateTime($content['updated_at']),
            count($content['history']),
            $content['html_url']
        );

        return new GistResource($file['content'], $metadata);
    }

    /**
     * Try to retrieve a token from user config or composer's auth.json file for the given Resource.
     *
     * @return null|string
     */
    private function getOAuthToken(AuthenticableResourceInterface $resource)
    {
        $securityToken = $this->authenticationStorage->get($resource->getKey());
        if ($securityToken instanceof Token) {
            $attributes = $securityToken->getAttributes();
            if (isset($attributes['oauth_token'])) {
                return $attributes['oauth_token'];
            }
        }

        if (file_exists($path = Composer::getComposerHomeDir().'/auth.json')) {
            $authJson = json_decode(file_get_contents($path), true);
            if (isset($authJson['github-oauth']['github.com'])) {
                return $authJson['github-oauth']['github.com'];
            }
        }
    }
}
