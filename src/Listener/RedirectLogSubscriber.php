<?php

namespace LastCall\Crawler\Listener;

use LastCall\Crawler\CrawlerEvents;
use LastCall\Crawler\Entity\Redirect;
use LastCall\Crawler\Event\CrawlerResponseEvent;
use Psr\Log\LoggerInterface;

class RedirectLogSubscriber extends RedirectSubscriber
{

    /**
     * @var \Psr\Log\LoggerInterface
     */
    private $logger;

    /**
     * {@inheritDoc}
     */
    public static function getSubscribedEvents()
    {
        return array(
          CrawlerEvents::SUCCESS => 'onRequestSuccess',
        );
    }

    public function __construct(LoggerInterface $logger)
    {
        $this->logger = $logger;
    }

    public function onRequestSuccess(CrawlerResponseEvent $event)
    {
        $response = $event->getResponse();
        $request = $event->getRequest();

        if (in_array($response->getStatusCode(),
            self::$redirectCodes) && $response->hasHeader('Location')
        ) {
            $urlHandler = $event->getUrlHandler();
            $location = $urlHandler->absolutizeUrl($response->getHeaderLine('Location'));

            $this->logger->info(sprintf('Redirect %s detected from %s to %s', $response->getStatusCode(), $request->getUri(), $location), [
                'from' => $request->getUri(),
                'to' => $location
            ]);
        }
    }


}