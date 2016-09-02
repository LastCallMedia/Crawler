<?php

namespace LastCall\Crawler\Configuration;

use GuzzleHttp\Client;
use GuzzleHttp\Psr7\Request;
use LastCall\Crawler\Common\OutputAwareInterface;
use LastCall\Crawler\Common\SetupTeardownInterface;
use LastCall\Crawler\Configuration\ServiceProvider\FragmentServiceProvider;
use LastCall\Crawler\Configuration\ServiceProvider\LoggerServiceProvider;
use LastCall\Crawler\Configuration\ServiceProvider\MatcherServiceProvider;
use LastCall\Crawler\Configuration\ServiceProvider\NormalizerServiceProvider;
use LastCall\Crawler\Configuration\ServiceProvider\QueueServiceProvider;
use LastCall\Crawler\CrawlerEvents;
use LastCall\Crawler\Handler\Reporting\CrawlerStatusReporter;
use LastCall\Crawler\Reporter\ConsoleOutputReporter;
use Pimple\Container;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * A crawler configuration based on the Pimple DI container.
 */
class Configuration extends Container implements ConfigurationInterface, OutputAwareInterface
{
    public function __construct($baseUrl = null)
    {
        parent::__construct();
        $this['base_url'] = $baseUrl;
        $this['client'] = function () {
            return new Client(['allow_redirects' => false]);
        };
        $this['listeners'] = function () {
            return [];
        };
        $this['subscribers'] = function () {
            return [
                'reporter' => new CrawlerStatusReporter($this['queue'], $this['reporters']),
            ];
        };
        $this['reporters'] = function () {
            $reporters = array();
            if ($output = $this['output']) {
                $reporters['console'] = new ConsoleOutputReporter($output);
            }

            return $reporters;
        };
        $this['output'] = false;

        $this->register(new QueueServiceProvider());
        $this->register(new MatcherServiceProvider());
        $this->register(new NormalizerServiceProvider());
        $this->register(new LoggerServiceProvider());
        $this->register(new FragmentServiceProvider());

        // On start, add the default request.
        $this->addListener(CrawlerEvents::START, function () {
            $this['queue']->push(new Request('GET', $this['base_url']));
        });
        $this->addListener(CrawlerEvents::SETUP, function () {
            if ($this['queue'] instanceof SetupTeardownInterface) {
                $this['queue']->onSetup();
            }
        });
        $this->addListener(CrawlerEvents::TEARDOWN, function () {
            if ($this['queue'] instanceof SetupTeardownInterface) {
                $this['queue']->onTeardown();
            }
        });
    }

    public function setOutput(OutputInterface $output)
    {
        $this['output'] = $output;
    }

    public function getQueue()
    {
        return $this['queue'];
    }

    public function getClient()
    {
        return $this['client'];
    }

    public function getListeners()
    {
        return $this['listeners'];
    }

    public function getSubscribers()
    {
        return $this['subscribers'];
    }

    public function addListener($eventName, callable $callback, $priority = 0)
    {
        $this->extend('listeners',
            function (array $listeners) use ($eventName, $callback, $priority) {
                $listeners[$eventName][] = [$callback, $priority];

                return $listeners;
            });
    }

    public function addSubscriber(EventSubscriberInterface $subscriber)
    {
        $this->extend('subscribers', function (array $subscribers) use ($subscriber) {
            $subscribers[] = $subscriber;

            return $subscribers;
        });
    }
}
