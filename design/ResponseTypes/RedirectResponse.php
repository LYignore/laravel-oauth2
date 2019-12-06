<?php
namespace Lyignore\LaravelOauth2\Design\ResponseTypes;

use Lyignore\LaravelOauth2\Design\Entities\RefreshTokenEntityInterface;

class RedirectResponse extends AbstractResponse
{
    private $redirectUri;

    public function setRedirectUri($redirectUri)
    {
        $this->redirectUri = $redirectUri;
    }

    public function generateResponse()
    {
        header('Location:'.$this->redirectUri);
    }
}