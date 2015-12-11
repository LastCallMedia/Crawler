<?php

namespace LastCall\Crawler\Listener;

use LastCall\Crawler\CrawlerEvents;
use LastCall\Crawler\Entity\RequestLog;
use LastCall\Crawler\Event\CrawlerEvent;
use LastCall\Crawler\Event\CrawlerResponseEvent;
use LastCall\Crawler\Url\TraceableUri;
use Psr\Log\LoggerInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class RequestLogSubscriber implements EventSubscriberInterface
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
            CrawlerEvents::SENDING => 'onRequestSending',
            CrawlerEvents::FAIL => 'onRequestComplete',
            CrawlerEvents::SUCCESS => 'onRequestComplete',
        );
    }

    public function __construct(LoggerInterface $logger)
    {
        $this->logger = $logger;
    }

    public function onRequestSending(CrawlerEvent $event)
    {
        $uri = $event->getRequest()->getUri();

        $this->logger->info(sprintf('Sending %s', $uri));
    }

    public function onRequestComplete(CrawlerResponseEvent $event)
    {
        $uri = $event->getRequest()->getUri();
        $context = [];
        if ($uri instanceof TraceableUri) {
            $context = array(
                'previous' => (string) $uri->getPrevious(),
                'next' => (string) $uri->getNext(),
            );
        }

        $message = sprintf('Received %d for %s',
            $event->getResponse()->getStatusCode(),
            $event->getRequest()->getUri());
        $this->logger->info($message, $context);
    }

}