<?php


namespace LastCall\Crawler\Handler\Logging;


use LastCall\Crawler\CrawlerEvents;
use LastCall\Crawler\Event\CrawlerExceptionEvent;
use Psr\Log\LoggerInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * Logs exceptions to a PSR-3 compatible logger.
 */
class ExceptionLogger implements EventSubscriberInterface
{

    /**
     * @var \Psr\Log\LoggerInterface
     */
    private $logger;

    public static function getSubscribedEvents()
    {
        return [
            CrawlerEvents::EXCEPTION => 'onCrawlerException',
        ];
    }

    public function __construct(LoggerInterface $logger)
    {
        $this->logger = $logger;
    }

    public function onCrawlerException(CrawlerExceptionEvent $event)
    {
        $this->logger->critical($event->getException(), [
            'exception' => $event->getException(),
            'url' => $event->getRequest()->getUri(),
        ]);
    }
}