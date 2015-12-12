<?php


namespace LastCall\Crawler\Handler;


use Psr\Http\Message\ResponseInterface;

trait RedirectDetectionTrait
{

    protected function isRedirectResponse(ResponseInterface $response)
    {
        $codes = [201, 301, 302, 303, 307, 308];

        return in_array($response->getStatusCode(),
            $codes) && $response->hasHeader('Location');
    }

}