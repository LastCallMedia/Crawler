<?php

namespace LastCall\Crawler\Configuration;

use GuzzleHttp\Client;
use GuzzleHttp\Psr7\Uri;
use LastCall\Crawler\Common\OutputAwareInterface;
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
        parent::__construct([
            'normalizers' => [],
        ]);
        $this['baseUrl'] = $baseUrl;
        $this['queue'] = function () {
            if (isset($this['doctrine'])) {
                return new DoctrineRequestQueue($this['doctrine']);
            }

            return new ArrayRequestQueue();
        };
        $this['client'] = function () {
            return new Client(['allow_redirects' => false]);
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
            $matcher = Matcher::create();
            $matcher->schemeIs($baseUri->getScheme());
            $matcher->hostIs($baseUri->getHost());
            return $matcher;
        };
        $this['html_matcher'] = function() {
            $matcher = clone $this['matcher'];
            $matcher->pathExtensionIs($this['html_extensions']);
            return $matcher;
        };
        $this['normalizer'] = function () {
            return new Normalizer($this['normalizers'], true);
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
    }

    public function setOutput(OutputInterface $output)
    {
        $this['output'] = $output;
    }

    public function getBaseUrl()
    {
        return $this['baseUrl'];
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
