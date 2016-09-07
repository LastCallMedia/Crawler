<?php

namespace LastCall\Crawler\Handler;

use LastCall\Crawler\CrawlerEvents;
use LastCall\Crawler\Queue\RequestQueueInterface;
use Symfony\Component\Console\Helper\ProgressIndicator;
use Symfony\Component\Console\Style\OutputStyle;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class CrawlMonitor implements EventSubscriberInterface
{
    /**
     * @var \Symfony\Component\Console\Style\OutputStyle
     */
    private $io;

    /**
     * @var array
     */
    private $stats = [
        'sent' => 0,
        'success' => 0,
        'failure' => 0,
        'exception' => 0,
    ];

    private $indicator;

    public static function getSubscribedEvents()
    {
        return [
            CrawlerEvents::SETUP => 'onSetup',
            CrawlerEvents::TEARDOWN => 'onTeardown',
            CrawlerEvents::START => 'onStart',
            CrawlerEvents::FINISH => 'onFinish',
            CrawlerEvents::SENDING => 'onSending',
            CrawlerEvents::SUCCESS => 'onSuccess',
            CrawlerEvents::FAILURE => 'onFailure',
            CrawlerEvents::EXCEPTION => 'onException',
        ];
    }

    public function __construct(RequestQueueInterface $queue, OutputStyle $io)
    {
        $this->io = $io;
        $this->queue = $queue;
    }

    public function onSetup()
    {
        $this->io->success('Setup complete');
    }

    public function onTeardown()
    {
        $this->io->success('Teardown complete');
    }

    public function onStart()
    {
        $this->indicator = new ProgressIndicator($this->io);
        $this->indicator->start('');
    }

    public function onFinish()
    {
        $this->indicator->finish('Complete');
    }

    public function onSending()
    {
        ++$this->stats['sent'];
        $this->report($this->stats);
    }

    public function onSuccess()
    {
        ++$this->stats['success'];
    }

    public function onFailure()
    {
        ++$this->stats['failure'];
    }

    public function onException()
    {
        ++$this->stats['exception'];
    }

    /**
     * @return \Symfony\Component\Console\Helper\ProgressIndicator
     */
    public function getIndicator()
    {
        if (!$this->indicator) {
            $this->indicator = new ProgressIndicator($this->io);
            $this->indicator->start('');
        }

        return $this->indicator;
    }

    public function report(array $stats)
    {
        $indicator = $this->getIndicator();
        $indicator->advance();

        $message = strtr('Crawling {{sent}} sent - {{remaining}} left', [
            '{{sent}}' => $stats['sent'],
            '{{remaining}}' => $this->queue->count(),
        ]);
        $indicator->setMessage($message);
    }
}
