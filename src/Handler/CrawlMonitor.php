<?php

namespace LastCall\Crawler\Handler;

use LastCall\Crawler\Common\SetupTeardownInterface;
use LastCall\Crawler\CrawlerEvents;
use LastCall\Crawler\Queue\RequestQueueInterface;
use Symfony\Component\Console\Helper\ProgressIndicator;
use Symfony\Component\Console\Style\OutputStyle;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * Watches and reports on a crawler session.
 */
class CrawlMonitor implements EventSubscriberInterface, SetupTeardownInterface
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

    /**
     * @var \LastCall\Crawler\Queue\RequestQueueInterface
     */
    private $queue;

    /**
     * @var ProgressIndicator
     */
    private $indicator;

    /**
     * {@inheritdoc}
     */
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

    /**
     * Execute startup tasks.
     */
    public function onStart()
    {
        $this->getIndicator();
    }

    /**
     * Execute finish tasks.
     */
    public function onFinish()
    {
        $this->getIndicator()->finish('Complete');
    }

    /**
     * Execute sending tasks.
     */
    public function onSending()
    {
        ++$this->stats['sent'];
        $this->report($this->stats);
    }

    /**
     * Update the statistics to note a request success.
     */
    public function onSuccess()
    {
        ++$this->stats['success'];
    }

    /**
     * Update the statistics to note a request failure.
     */
    public function onFailure()
    {
        ++$this->stats['failure'];
    }

    /**
     * Update the statistics to note an exception.
     */
    public function onException()
    {
        ++$this->stats['exception'];
    }

    /**
     * Get the progress indicator.
     *
     * @return \Symfony\Component\Console\Helper\ProgressIndicator
     */
    private function getIndicator()
    {
        if (!$this->indicator) {
            $this->indicator = new ProgressIndicator($this->io);
            $this->indicator->start('Starting...');
        }

        return $this->indicator;
    }

    /**
     * Report statistics to the console output.
     *
     * @param array $stats
     */
    private function report(array $stats)
    {
        $indicator = $this->getIndicator();
        $indicator->advance();

        $message = strtr('Crawling... {{sent}} sent - {{remaining}} left', [
            '{{sent}}' => $stats['sent'],
            '{{remaining}}' => $this->queue->count(),
        ]);
        $indicator->setMessage($message);
    }
}
