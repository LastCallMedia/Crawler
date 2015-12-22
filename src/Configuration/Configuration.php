<?php


namespace LastCall\Crawler\Configuration;


use GuzzleHttp\Client;
use LastCall\Crawler\Common\OutputAwareInterface;
use LastCall\Crawler\Handler\Logging\ExceptionLogger;
use LastCall\Crawler\Handler\Logging\RequestLogger;
use LastCall\Crawler\Handler\Module\ModuleHandler;
use LastCall\Crawler\Module\Parser\CSSSelectorParser;
use LastCall\Crawler\Module\Parser\XPathParser;
use LastCall\Crawler\Module\Processor\LinkProcessor;
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
            'normalizers' => []
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
                'moduleHandler' => new ModuleHandler($this['parsers'],
                    $this['processors'])
            ];

            if (isset($this['logger'])) {
                $subscribers['requestLogger'] = new RequestLogger($this['logger']);
                $subscribers['exceptionLogger'] = new ExceptionLogger($this['logger']);
            }

            return $subscribers;
        };
        $this['matcher'] = function () {
            return new Matcher(['^' . $this['baseUrl']]);
        };
        $this['normalizer'] = function () {
            return new Normalizer($this['normalizers']);
        };
        $this['normalizers'] = [];
        $this['parsers'] = function () {
            $parsers = [
                'xpath' => new XPathParser()
            ];
            if (class_exists('Symfony\Component\CssSelector\CssSelectorConverter')) {
                $parsers['css'] = new CSSSelectorParser();
            }

            return $parsers;
        };
        $this['processors'] = function () {
            return [
                'link' => new LinkProcessor($this['matcher'],
                    $this['normalizer'])
            ];
        };
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