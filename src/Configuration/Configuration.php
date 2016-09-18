<?php

namespace LastCall\Crawler\Configuration;

use GuzzleHttp\Client;
use GuzzleHttp\Psr7\Request;
use LastCall\Crawler\Common\OutputAwareInterface;
use LastCall\Crawler\Common\SetupTeardownInterface;
use LastCall\Crawler\Configuration\ServiceProvider\LoggerServiceProvider;
use LastCall\Crawler\Configuration\ServiceProvider\NormalizerServiceProvider;
use LastCall\Crawler\Configuration\ServiceProvider\RecursionServiceProvider;
use LastCall\Crawler\Configuration\ServiceProvider\MatcherServiceProvider;
use LastCall\Crawler\CrawlerEvents;
use LastCall\Crawler\Event\CrawlerStartEvent;
use LastCall\Crawler\Handler\HtmlRedispatcher;
use LastCall\Crawler\Queue\ArrayRequestQueue;
use LastCall\Crawler\Queue\DoctrineRequestQueue;
use Pimple\Container;
use Psr\Log\NullLogger;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

/**
 * A crawler configuration based on the Pimple DI container.
 */
class Configuration extends Container implements ConfigurationInterface, OutputAwareInterface
{
    public function __construct($baseUrl = null, array $config = array())
    {
        parent::__construct();
        $this['base_url'] = $baseUrl;
        $this['output'] = null;
        $this['client'] = function () {
            return new Client(['allow_redirects' => false]);
        };

        $this['queue'] = function () {
            if (isset($this['doctrine'])) {
                return new DoctrineRequestQueue($this['doctrine']);
            }

            return new ArrayRequestQueue();
        };
        $this['logger'] = function () {
            return new NullLogger();
        };
        $this['redispatcher'] = function () {
            return new HtmlRedispatcher();
        };
        $this['loggers'] = [];
        $this['discoverers'] = [];
        $this['recursors'] = [];

        $this->configure($config);
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
        $dispatcher->addSubscriber($this['redispatcher']);
        $dispatcher->addListener(CrawlerEvents::SETUP, function () {
            if ($this['queue'] instanceof SetupTeardownInterface) {
                $this['queue']->onSetup();
            }
        });
        $dispatcher->addListener(CrawlerEvents::TEARDOWN, function () {
            if ($this['queue'] instanceof SetupTeardownInterface) {
                $this['queue']->onTeardown();
            }
        });
        // Add the initial request to the queue.
        $dispatcher->addListener(CrawlerEvents::START, function (CrawlerStartEvent $event) {
            $event->addAdditionalRequest(new Request('GET', $this['base_url']));
        });
        foreach ($this->getLoggers() as $logger) {
            $dispatcher->addSubscriber($logger);
        }
        foreach ($this->getDiscoverers() as $discoverer) {
            $dispatcher->addSubscriber($discoverer);
        }
        foreach ($this->getRecursors() as $recursor) {
            $dispatcher->addSubscriber($recursor);
        }
    }

    /**
     * Configure additional services required for the configuration before
     * layering in the passed in configuration.
     *
     * @param array $config
     */
    protected function configure(array $config = array())
    {
        foreach ($this->getProviders() as $provider) {
            $this->register($provider);
        }
        foreach ($config as $key => $value) {
            $this[$key] = $value;
        }
    }

    /**
     * Gets a list of Pimple service providers this configuration will add.
     *
     * @return array
     */
    protected function getProviders()
    {
        return [
            'logger' => new LoggerServiceProvider(),
            'normalizer' => new NormalizerServiceProvider(),
            'matcher' => new MatcherServiceProvider(),
            'recursion' => new RecursionServiceProvider(),
        ];
    }

    private function getLoggers()
    {
        return array_map([$this, 'getLogger'], $this['loggers']);
    }

    private function getDiscoverers()
    {
        return array_map([$this, 'getDiscoverer'], $this['discoverers']);
    }

    private function getRecursors()
    {
        return array_map([$this, 'getRecursor'], $this['recursors']);
    }

    private function getLogger($id)
    {
        if (isset($this['logger.'.$id])) {
            return $this['logger.'.$id];
        }
        throw new \InvalidArgumentException(sprintf('Unknown logger: %s', $id));
    }

    private function getDiscoverer($id)
    {
        if (isset($this['discoverer.'.$id])) {
            return $this['discoverer.'.$id];
        }
        throw new \InvalidArgumentException(sprintf('Unknown discoverer: %s', $id));
    }

    private function getRecursor($id)
    {
        if (isset($this['recursor.'.$id])) {
            return $this['recursor.'.$id];
        }
        throw new \InvalidArgumentException(sprintf('Unknown recursor: %s', $id));
    }
}
