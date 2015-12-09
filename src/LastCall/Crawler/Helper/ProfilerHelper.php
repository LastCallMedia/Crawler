<?php

namespace LastCall\Crawler\Helper;


use Symfony\Component\Console\Helper\Helper;
use Symfony\Component\Console\Style\OutputStyle;
use Symfony\Component\EventDispatcher\Debug\TraceableEventDispatcher;
use Symfony\Component\Stopwatch\Stopwatch;

class ProfilerHelper extends Helper
{
    /**
     * @var \Symfony\Component\Stopwatch\Stopwatch
     */
    private $stopwatch;

    /**
     * @var \Symfony\Component\EventDispatcher\Debug\TraceableEventDispatcher
     */
    private $dispatcher;

    public function getName() {
        return 'profiler';
    }

    public function getTraceableDispatcher($dispatcher) {
        $this->stopwatch = new Stopwatch();
        $this->dispatcher = new TraceableEventDispatcher($dispatcher, $this->stopwatch);
        return $this->dispatcher;
    }

    public function renderProfile(OutputStyle $io) {
        $headers = array('Listener', 'Time');
        $rows = array();
        foreach($this->stopwatch->getSections() as $section) {
            foreach($section->getEvents() as $eventName => $event) {
                $rows[] = [$eventName, $event->getDuration()];
            }
        }
        $io->table($headers, $rows);
    }
}