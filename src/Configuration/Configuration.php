<?php

namespace LastCall\Crawler\Configuration;

use GuzzleHttp\Client;
use GuzzleHttp\Psr7\Request;
use GuzzleHttp\Psr7\Uri;
use LastCall\Crawler\Common\OutputAwareInterface;
use LastCall\Crawler\CrawlerEvents;
use LastCall\Crawler\Fragment\Parser\CSSSelectorParser;
use LastCall\Crawler\Fragment\Parser\XPathParser;
use LastCall\Crawler\Fragment\Processor\LinkProcessor;
use LastCall\Crawler\Handler\Fragment\FragmentHandler;
use LastCall\Crawler\Handler\Logging\ExceptionLogger;
use LastCall\Crawler\Handler\Logging\RequestLogger;
use LastCall\Crawler\Queue\ArrayRequestQueue;
use LastCall\Crawler\Queue\DoctrineRequestQueue;
use LastCall\Crawler\Uri\Matcher;
use LastCall\Crawler\Uri\Normalizer;
use Pimple\Container;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * A crawler configuration based on the Pimple DI container.
 */
class Configuration extends Container implements ConfigurationInterface, OutputAwareInterface
{
    public function __construct($baseUrl = null)
    {
        parent::__construct();
        $this['baseUrl'] = $baseUrl;
        $this['queue'] = function () {
            if (isset($this['doctrine'])) {
                $queue = new DoctrineRequestQueue($this['doctrine']);
            }
            else {
                $queue = new ArrayRequestQueue();
            }
            return $queue;
        };
        $this['client'] = function () {
            return new Client(['allow_redirects' => false]);
        };
        $this['normalizers'] = function() {
            return [];
        };
        $this['listeners'] = function () {
            return [];
        };
        $this['subscribers'] = function () {
            $subscribers = [
                'moduleHandler' => new FragmentHandler($this['parsers'],
                    $this['processors']),
            ];

            if (isset($this['logger'])) {
                $subscribers['requestLogger'] = new RequestLogger($this['logger']);
                $subscribers['exceptionLogger'] = new ExceptionLogger($this['logger']);
            }

            return $subscribers;
        };
        $this['matcher'] = function () {
            $baseUri = new Uri($this['baseUrl']);
            $matcher = Matcher::all()
                ->schemeIs($baseUri->getScheme())
                ->hostIs($baseUri->getHost());

            return $matcher;
        };
        $this['html_matcher'] = function () {
            $matcher = clone $this['matcher'];
            $matcher->pathExtensionIs($this['html_extensions']);

            return $matcher;
        };
        $this['normalizer'] = function () {
            return new Normalizer($this['normalizers'], $this['matcher']);
        };
        $this['normalizers'] = [];
        $this['parsers'] = function () {
            $parsers = [
                'xpath' => new XPathParser(),
            ];
            if (class_exists('Symfony\Component\CssSelector\CssSelectorConverter')) {
                $parsers['css'] = new CSSSelectorParser();
            }

            return $parsers;
        };
        $this['processors'] = function () {
            return [
                'link' => new LinkProcessor($this['html_matcher'],
                    $this['normalizer']),
            ];
        };
        $this['html_extensions'] = ['', 'html', 'htm', 'php', 'asp', 'aspx', 'cfm'];

        // On start, add the default request.
        $this->addListener(CrawlerEvents::START, function() {
            $this['queue']->push(new Request('GET', $this['baseUrl']));
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
}
