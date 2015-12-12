<?php


namespace LastCall\Crawler\Handler\Logging;


use LastCall\Crawler\CrawlerEvents;
use LastCall\Crawler\Event\CrawlerEvent;
use LastCall\Crawler\Event\CrawlerResponseEvent;
use LastCall\Crawler\Handler\CrawlerHandlerInterface;
use LastCall\Crawler\Url\TraceableUri;
use Psr\Log\LoggerInterface;

class RequestLogger implements CrawlerHandlerInterface
{
    private $logger;

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

    private function getUri(CrawlerEvent $event) {
        return $event->getRequest()->getUri();
    }

    private function getStatus(CrawlerResponseEvent $event) {
        return (int)$event->getResponse()->getStatusCode();
    }

    private function isRedirectResponse(CrawlerResponseEvent $event) {
        $codes = [201, 301, 302, 303, 307, 308];
        return in_array($event->getResponse()->getStatusCode(), $codes)
            && $event->getResponse()->hasHeader('Location');
    }

    private function getRedirectUri(CrawlerResponseEvent $event) {
        return $event->getResponse()->getHeaderLine('Location');
    }

    public function onSending(CrawlerEvent $event) {
        $uri = $this->getUri($event);

        if($uri instanceof TraceableUri && $uri->getNext()) {
            $this->logger->debug(sprintf('Sending %s as a retry for %s', $uri, $uri->getNext()), [
                'url' => (string)$uri,
                'retry' => (string)$uri->getNext()
            ]);
        }
        else {
            $this->logger->debug(sprintf('Sending %s', $uri), [
                'url' => (string)$uri
            ]);
        }
    }

    public function onSuccess(CrawlerEvent $event) {
        $uri = $this->getUri($event);
        $status = $this->getStatus($event);
        if($this->isRedirectResponse($event)) {
            $redirectUri = $this->getRedirectUri($event);
            $this->logger->info(sprintf('Received %s redirecting to %s', $uri, $redirectUri), [
                'url' => (string)$uri,
                'status' => $status,
                'redirect' => (string)$redirectUri,
            ]);
        }
        else {
            $this->logger->debug(sprintf('Received %s', $uri), [
                'url' => (string)$uri,
                'status' => $status,
            ]);
        }
    }

    public function onFailure(CrawlerEvent $event) {
        $uri = $this->getUri($event);
        $status = $this->getStatus($event);
        $this->logger->warning(sprintf('Failure %s', $uri), [
            'url' => (string)$uri,
            'status' => $status,
        ]);
    }
}