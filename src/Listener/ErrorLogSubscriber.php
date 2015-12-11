<?php

namespace LastCall\Crawler\Listener;

use LastCall\Crawler\Crawler;
use LastCall\Crawler\Event\CrawlerExceptionEvent;
use Psr\Log\LoggerInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class ErrorLogSubscriber implements EventSubscriberInterface
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
          Crawler::EXCEPTION => 'onRequestException',
        );
    }

    public function __construct(LoggerInterface $logger)
    {
        $this->logger = $logger;
    }

    public function onRequestException(CrawlerExceptionEvent $event)
    {
        $this->logger->error($event->getException(), array(
          'url' => (string) $event->getRequest()->getUri()
        ));
    }
}