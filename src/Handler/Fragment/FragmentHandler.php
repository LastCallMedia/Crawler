<?php

namespace LastCall\Crawler\Handler\Fragment;

use LastCall\Crawler\Common\SetupTeardownInterface;
use LastCall\Crawler\CrawlerEvents;
use LastCall\Crawler\Event\CrawlerResponseEvent;
use LastCall\Crawler\Fragment\FragmentSubscription;
use LastCall\Crawler\Fragment\Parser\FragmentParserInterface;
use LastCall\Crawler\Fragment\Processor\FragmentProcessorInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * Delegates parsing and processing of subsections (modules)
 * of the response.
 */
class FragmentHandler implements EventSubscriberInterface
{
    /**
     * {@inheritdoc}
     */
    public static function getSubscribedEvents()
    {
        return [
            CrawlerEvents::SUCCESS => 'onSuccess',
            CrawlerEvents::SETUP => 'onSetup',
            CrawlerEvents::TEARDOWN => 'onTeardown',
        ];
    }

    /**
     * @var \LastCall\Crawler\Fragment\Parser\FragmentParserInterface[]
     */
    private $parsers = [];

    /**
     * @var \LastCall\Crawler\Fragment\Processor\FragmentProcessorInterface[]
     */
    private $processors = [];

    /**
     * @var \LastCall\Crawler\Fragment\FragmentSubscription[]
     */
    private $subscribed = [];

    /**
     * FragmentHandler constructor.
     *
     * @param array $parsers
     * @param array $processors
     */
    public function __construct(array $parsers = [], array $processors = [])
    {
        foreach ($parsers as $parser) {
            $this->addParser($parser);
        }
        foreach ($processors as $processor) {
            $this->addProcessor($processor);
        }
    }

    /**
     * Add a parser to the handler.
     *
     * @param \LastCall\Crawler\Fragment\Parser\FragmentParserInterface $parser
     */
    public function addParser(FragmentParserInterface $parser)
    {
        $this->parsers[$parser->getId()] = $parser;
    }

    /**
     * Add a processor to the handler.
     *
     * @param \LastCall\Crawler\Fragment\Processor\FragmentProcessorInterface $processor
     */
    public function addProcessor(FragmentProcessorInterface $processor)
    {
        $this->processors[] = $processor;
        $this->subscribed = array_merge($this->subscribed,
            $this->getSubscribedMethods($processor));
    }

    /**
     * @param \LastCall\Crawler\Fragment\Processor\FragmentProcessorInterface $processor
     *
     * @return \LastCall\Crawler\Fragment\FragmentSubscription[]
     */
    private function getSubscribedMethods(FragmentProcessorInterface $processor)
    {
        $methods = $processor->getSubscribedMethods();
        if (!is_array($methods)) {
            throw new \InvalidArgumentException(sprintf('%s::getSubscribedMethods must return an array.',
                get_class($processor)));
        }
        foreach ($methods as $method) {
            if (!$method instanceof FragmentSubscription) {
                throw new \InvalidArgumentException('Invalid module subscription');
            }
            if (!$method->getParserId()) {
                throw new \InvalidArgumentException('No parser was specified');
            }
            // Check that the parser is valid.
            $this->getParser($method->getParserId());
        }

        return $methods;
    }

    /**
     * @return array
     */
    private function getSubscribersGrouped()
    {
        $grouped = [];
        foreach ($this->subscribed as $subscription) {
            $grouped[$subscription->getParserId()][$subscription->getSelector()][] = $subscription;
        }

        return $grouped;
    }

    /**
     * @param $parserId
     *
     * @return \LastCall\Crawler\Fragment\Parser\FragmentParserInterface
     */
    private function getParser($parserId)
    {
        if (!isset($this->parsers[$parserId])) {
            throw new \InvalidArgumentException(sprintf('Invalid parser %s',
                $parserId));
        }

        return $this->parsers[$parserId];
    }

    private function getSetupTeardownObjects()
    {
        $objects = [];
        foreach ($this->processors as $processor) {
            if ($processor instanceof SetupTeardownInterface) {
                $objects[] = $processor;
            }
        }

        return $objects;
    }

    /**
     * @param \LastCall\Crawler\Event\CrawlerResponseEvent $event
     */
    public function onSuccess(CrawlerResponseEvent $event)
    {
        // A processor should be able to define a parser, and register
        // a selector for a dom snippet it's interested in.

        foreach ($this->getSubscribersGrouped() as $parserId => $parserSubscriptions) {
            $parser = $this->getParser($parserId);
            $node = $parser->prepareResponse($event->getResponse());
            foreach ($parserSubscriptions as $selector => $subscriptions) {
                $fragments = $parser->parseFragments($node, $selector);
                foreach ($subscriptions as $subscription) {
                    $callable = $subscription->getCallable();
                    $callable($event, $fragments);
                }
            }
        }
    }

    public function onSetup()
    {
        foreach ($this->getSetupTeardownObjects() as $object) {
            $object->onSetup();
        }
    }

    public function onTeardown()
    {
        foreach ($this->getSetupTeardownObjects() as $object) {
            $object->onTeardown();
        }
    }
}
