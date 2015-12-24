<?php

namespace LastCall\Crawler\Handler\Reporting;

use LastCall\Crawler\CrawlerEvents;
use LastCall\Crawler\Queue\RequestQueueInterface;
use LastCall\Crawler\Reporter\ReporterInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class CrawlerStatusReporter implements EventSubscriberInterface
{
    private $stats = [
        'sent' => 0,
        'success' => 0,
        'failure' => 0,
        'exception' => 0,
    ];

    private $queue;

    /**
     * @var \LastCall\Crawler\Reporter\ReporterInterface[]
     */
    private $targets = [];

    public static function getSubscribedEvents()
    {
        return [
            CrawlerEvents::SENDING => 'onSending',
            CrawlerEvents::SUCCESS => 'onSuccess',
            CrawlerEvents::FAILURE => 'onFailure',
            CrawlerEvents::EXCEPTION => 'onException',
        ];
    }

    /**
     * CrawlerStatusReporter constructor.
     *
     * @param \LastCall\Crawler\Reporter\ReporterInterface[] $targets
     */
    public function __construct(
        RequestQueueInterface $queue,
        array $targets = []
    ) {
        $this->queue = $queue;
        foreach ($targets as $target) {
            $this->addTarget($target);
        }
    }

    public function addTarget(ReporterInterface $reporter)
    {
        $this->targets[] = $reporter;
    }

    public function onSending()
    {
        ++$this->stats['sent'];
        $this->report();
    }

    public function onSuccess()
    {
        ++$this->stats['success'];
        $this->report();
    }

    public function onFailure()
    {
        ++$this->stats['failure'];
        $this->report();
    }

    public function onException()
    {
        ++$this->stats['exception'];
        $this->report();
    }

    private function report()
    {
        $stats = $this->stats + [
                'remaining' => $this->queue->count(RequestQueueInterface::FREE),
            ];
        foreach ($this->targets as $target) {
            $target->report($stats);
        }
    }
}
