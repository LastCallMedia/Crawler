<?php

namespace LastCall\Crawler\Handler\Logging;

use LastCall\Crawler\Common\RedirectDetectionTrait;
use LastCall\Crawler\CrawlerEvents;
use LastCall\Crawler\Event\CrawlerRequestEvent;
use LastCall\Crawler\Event\CrawlerResponseEvent;
use Psr\Log\LoggerInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * Logs requests sent and completed to a PSR-3 compatible logger.
 */
class RequestLogger implements EventSubscriberInterface
{
    use RedirectDetectionTrait;

    /**
     * @var \Psr\Log\LoggerInterface
     */
    private $logger;

    /**
     * {@inheritdoc}
     */
    public static function getSubscribedEvents()
    {
        return [
            CrawlerEvents::SENDING => 'onSending',
            CrawlerEvents::FAILURE => 'onFailure',
            CrawlerEvents::SUCCESS => 'onSuccess',
        ];
    }

    public function __construct(LoggerInterface $logger)
    {
        $this->logger = $logger;
    }

    private function getUri(CrawlerRequestEvent $event)
    {
        return $event->getRequest()->getUri();
    }

    private function getStatus(CrawlerResponseEvent $event)
    {
        return (int) $event->getResponse()->getStatusCode();
    }

    private function getRedirectUri(CrawlerResponseEvent $event)
    {
        return $event->getResponse()->getHeaderLine('Location');
    }

    /**
     * Log requests as they are sent.
     *
     * @param \LastCall\Crawler\Event\CrawlerRequestEvent $event
     */
    public function onSending(CrawlerRequestEvent $event)
    {
        $uri = $this->getUri($event);

        $this->logger->debug(sprintf('Sending %s', $uri), [
            'url' => (string) $uri,
        ]);
    }

    /**
     * Log successful responses.
     *
     * @param \LastCall\Crawler\Event\CrawlerResponseEvent $event
     */
    public function onSuccess(CrawlerResponseEvent $event)
    {
        $uri = $this->getUri($event);
        $status = $this->getStatus($event);
        $response = $event->getResponse();
        if ($this->isRedirectResponse($response)) {
            $redirectUri = $this->getRedirectUri($event);
            $this->logger->info(sprintf('Received %s redirecting to %s', $uri,
                $redirectUri), [
                'url' => (string) $uri,
                'status' => $status,
                'redirect' => (string) $redirectUri,
            ]);
        } else {
            $this->logger->debug(sprintf('Received %s', $uri), [
                'url' => (string) $uri,
                'status' => $status,
            ]);
        }
    }

    /**
     * Log failed responses.
     *
     * @param \LastCall\Crawler\Event\CrawlerResponseEvent $event
     */
    public function onFailure(CrawlerResponseEvent $event)
    {
        $uri = $this->getUri($event);
        $status = $this->getStatus($event);
        $this->logger->warning(sprintf('Failure %s', $uri), [
            'url' => (string) $uri,
            'status' => $status,
        ]);
    }
}
