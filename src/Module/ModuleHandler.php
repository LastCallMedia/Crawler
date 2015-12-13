<?php


namespace LastCall\Crawler\Module;


use LastCall\Crawler\CrawlerEvents;
use LastCall\Crawler\Event\CrawlerResponseEvent;
use LastCall\Crawler\Handler\CrawlerHandlerInterface;
use LastCall\Crawler\Module\Parser\ModuleParserInterface;
use LastCall\Crawler\Module\Processor\ModuleProcessorInterface;

/**
 * ModuleHandler delegates parsing and processing of subsections (modules)
 * of the response.
 */
class ModuleHandler implements CrawlerHandlerInterface
{

    /**
     * {@inheritdoc}
     */
    public static function getSubscribedEvents()
    {
        return [
            CrawlerEvents::SUCCESS => 'onSuccess',
        ];
    }

    /**
     * @var \LastCall\Crawler\Module\Parser\ModuleParserInterface[]
     */
    private $parsers = [];

    /**
     * @var \LastCall\Crawler\Module\Processor\ModuleProcessorInterface[]
     */
    private $processors = [];

    /**
     * @var \LastCall\Crawler\Module\ModuleSubscription[]
     */
    private $subscribed = [];


    /**
     * ModuleHandler constructor.
     *
     * @param array $parsers
     * @param array $processors
     */
    public function __construct(array $parsers = [], array $processors = [])
    {
        foreach($parsers as $parser) {
            $this->addParser($parser);
        }
        foreach($processors as $processor) {
            $this->addProcessor($processor);
        }
    }

    /**
     * Add a parser to the handler.
     *
     * @param \LastCall\Crawler\Module\Parser\ModuleParserInterface $parser
     */
    public function addParser(ModuleParserInterface $parser) {
        $this->parsers[$parser->getId()] = $parser;
    }

    /**
     * Add a processor to the handler.
     *
     * @param \LastCall\Crawler\Module\Processor\ModuleProcessorInterface $processor
     */
    public function addProcessor(ModuleProcessorInterface $processor) {
        $this->processors[] = $processor;
        $this->subscribed = array_merge($this->subscribed, $this->getSubscribedMethods($processor));
    }

    /**
     * @param \LastCall\Crawler\Module\Processor\ModuleProcessorInterface $processor
     *
     * @return \LastCall\Crawler\Module\ModuleSubscription[]
     */
    private function getSubscribedMethods(ModuleProcessorInterface $processor) {
        $methods = $processor->getSubscribedMethods();
        if(!is_array($methods)) {
            throw new \InvalidArgumentException(sprintf('%s::getSubscribedMethods must return an array.', get_class($processor)));
        }
        foreach($methods as $method) {
            if(!$method instanceof ModuleSubscription) {
                throw new \InvalidArgumentException('Invalid module subscription');
            }
            if(!$method->getParserId()) {
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
    private function getSubscribersGrouped() {
        $grouped = [];
        foreach($this->subscribed as $subscription) {
            $grouped[$subscription->getParserId()][$subscription->getSelector()][] = $subscription;
        }
        return $grouped;
    }

    /**
     * @param $parserId
     *
     * @return \LastCall\Crawler\Module\Parser\ModuleParserInterface
     */
    private function getParser($parserId) {
        if(!isset($this->parsers[$parserId])) {
            throw new \InvalidArgumentException(sprintf('Invalid parser %s', $parserId));
        }
        return $this->parsers[$parserId];
    }

    /**
     * @param \LastCall\Crawler\Event\CrawlerResponseEvent $event
     */
    public function onSuccess(CrawlerResponseEvent $event) {
        // A processor should be able to define a parser, and register
        // a selector for a dom snippet it's interested in.

        foreach($this->getSubscribersGrouped() as $parserId => $parserSubscriptions) {
            $parser = $this->getParser($parserId);
            $node = $parser->parseResponse($event->getResponse());
            foreach($parserSubscriptions as $selector => $subscriptions) {
                $fragments = $parser->parseNodes($node, $selector);
                foreach($subscriptions as $subscription) {
                    $callable = $subscription->getCallable();
                    $callable($event, $fragments);
                }
            }
        }
    }
}