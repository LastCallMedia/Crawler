<?php

namespace LastCall\Crawler\Configuration;

use GuzzleHttp\Client;
use GuzzleHttp\Psr7\Request;
use LastCall\Crawler\Common\OutputAwareInterface;
use LastCall\Crawler\Configuration\ServiceProvider\FragmentServiceProvider;
use LastCall\Crawler\Configuration\ServiceProvider\LinkServiceProvider;
use LastCall\Crawler\Configuration\ServiceProvider\LoggerServiceProvider;
use LastCall\Crawler\Configuration\ServiceProvider\MatcherServiceProvider;
use LastCall\Crawler\Configuration\ServiceProvider\NormalizerServiceProvider;
use LastCall\Crawler\Configuration\ServiceProvider\QueueServiceProvider;
use LastCall\Crawler\CrawlerEvents;
use LastCall\Crawler\Event\CrawlerStartEvent;
use Pimple\Container;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

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
            return [];
        };
        $this['output'] = false;

        foreach($this->getProviders() as $provider) {
            $this->register($provider);
        }

        // On start, add the default request.
        $this->addListener(CrawlerEvents::START, function (CrawlerStartEvent $event) {
            $event->addAdditionalRequest(new Request('GET', $this['base_url']));
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

    public function attachToDispatcher(EventDispatcherInterface $dispatcher)
    {
        foreach ($this['subscribers'] as $subscriber) {
            $dispatcher->addSubscriber($subscriber);
        }
        foreach ($this['listeners'] as $eventName => $listeners) {
            foreach ($listeners as $listener) {
                $dispatcher->addListener($eventName, $listener[0], $listener[1]);
            }
        }
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

    protected function getProviders() {
        return [
            'queue' => new QueueServiceProvider(),
            'logger' => new LoggerServiceProvider(),
            'matcher' => new MatcherServiceProvider(),
            'normalizer' => new NormalizerServiceProvider(),
            'fragment' => new FragmentServiceProvider(),
            'link' => new LinkServiceProvider(),
        ];
    }
}
