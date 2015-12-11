<?php

namespace LastCall\Crawler\Listener;


use LastCall\Crawler\CrawlerEvents;
use LastCall\Crawler\Event\CrawlerEvent;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Stopwatch\Stopwatch;

class PerformanceSubscriber implements EventSubscriberInterface
{
    /**
     * @var int
     */
    private $interval;
    /**
     * @var int
     */
    private $sent = 0;

    /**
     * @var int
     */
    private $completed = 0;

    /**
     * @var \Symfony\Component\Stopwatch\Stopwatch
     */
    private $timer;

    /**
     * @var \Symfony\Component\Console\Output\OutputInterface
     */
    private $output;

    public static function getSubscribedEvents()
    {
        return [
            CrawlerEvents::SENDING => 'onSending',
            CrawlerEvents::SUCCESS => 'onComplete',
            CrawlerEvents::FAILURE => 'onComplete',
            CrawlerEvents::EXCEPTION => 'onComplete',
        ];
    }

    public function __construct(OutputInterface $output, $interval = 5)
    {
        $this->interval = $interval;
        $this->output = $output;
        $this->timer = new Stopwatch();
    }

    public function onSending(CrawlerEvent $event)
    {
        $this->sent++;
        $this->timer->start('crawler.request');
    }

    public function onComplete(CrawlerEvent $event)
    {
        $this->completed++;
        $this->timer->lap('crawler.request');

        if ($this->completed % $this->interval == 0) {
            $event = $this->timer->getEvent('crawler.request');
            $duration = $event->getDuration();
            $segments = count($event->getPeriods());
            $rate = $segments ? round($duration / $segments) : 0;

            $memory = $event->getMemory() / 1024 / 1024;

            $this->output->writeln(sprintf('Processed %s in %ss (%sms, %smb)',
                $segments, round($duration / 1000), $rate, $memory));
        }

    }

}