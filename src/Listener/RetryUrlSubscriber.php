<?php

namespace LastCall\Crawler\Listener;

use GuzzleHttp\Psr7\Request;
use LastCall\Crawler\CrawlerEvents;
use LastCall\Crawler\Event\CrawlerResponseEvent;
use LastCall\Crawler\Url\TraceableUri;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * This class exists to retry failed requests to URLs that we may have botched
 * the normalization of.
 *
 * For example, when we see a link to https://example.com/foo, we may try to
 * normalize that to http://example.com/foo, even though that page might not
 * exist.  To account for this, we keep a record of what the URL was before it
 * was normalized, and retry these requests to the original URL.
 *
 * In this class, we intercept any redirects or failed requests to a URL that
 * may have been normalized incorrectly, and attempt to reach the
 * pre-normalized version of the URL.
 */
class RetryUrlSubscriber implements EventSubscriberInterface
{

    /**
     * {@inheritDoc}
     */
    public static function getSubscribedEvents()
    {
        return array(
            CrawlerEvents::SUCCESS => 'onCrawlerSuccess',
            CrawlerEvents::FAIL => 'onCrawlerFail',
        );
    }

    /**
     * Act on redirect of a response.
     */
    public function onCrawlerSuccess(CrawlerResponseEvent $event)
    {
        $response = $event->getResponse();

        if (in_array($response->getStatusCode(),
                RedirectSubscriber::$redirectCodes) && $response->hasHeader('Location')
        ) {
            $this->addAlternateForms($event,
                $response->getHeaderLine('Location'));
        }
    }

    /**
     * @param \LastCall\Crawler\Event\CrawlerEvent $event
     */
    public function onCrawlerFail(CrawlerResponseEvent $event)
    {
        $this->addAlternateForms($event);
    }

    /**
     * Add the next alternate form of the URL to the queue to be tried.
     *
     * @param \LastCall\Crawler\Event\CrawlerEvent $event
     * @param string                               $limitTo
     */
    private function addAlternateForms(
        CrawlerResponseEvent $event,
        $limitTo = null
    ) {
        $request = $event->getRequest();
        $uri = $request->getUri();

        if ($uri instanceof TraceableUri) {
            if ($limitTo) {
                // Verify that where we're being asked to redirect to is one of the
                // previous forms of this URI.  Otherwise, we don't need to act here.
                while ($uri = $uri->getPrevious()) {
                    if ($limitTo === (string)$uri) {
                        $newRequest = new Request('GET', $uri);
                        $event->addAdditionalRequest($newRequest);

                        return;
                    }
                }
            } elseif ($uri = $uri->getPrevious()) {
                $newRequest = new Request('GET', $uri);
                $event->addAdditionalRequest($newRequest);
            }
        }
    }

}