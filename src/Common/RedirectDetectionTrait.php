<?php


namespace LastCall\Crawler\Common;


use Psr\Http\Message\ResponseInterface;

/**
 * Common methods for detecting whether a response was a redirect
 * response.
 */
trait RedirectDetectionTrait
{

    /**
     * Check whether the given response indicates a redirect.
     *
     * @param \Psr\Http\Message\ResponseInterface $response
     *
     * @return bool
     */
    protected function isRedirectResponse(ResponseInterface $response)
    {
        $codes = [201, 301, 302, 303, 307, 308];

        return in_array($response->getStatusCode(),
            $codes) && $response->hasHeader('Location');
    }

}