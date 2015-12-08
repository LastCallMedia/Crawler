<?php

namespace LastCall\Crawler\Listener;

use LastCall\Crawler\Crawler;
use LastCall\Crawler\Event\CrawlerResponseEvent;
use LastCall\Crawler\Module\ModuleParser;
use LastCall\Crawler\Module\ModuleProcessor;
use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * Process modules in the HTML by running them through a parser.
 */
class ModuleSubscriber implements EventSubscriberInterface
{

    /**
     * @var \LastCall\Crawler\Module\ModuleParser
     */
    private $parser;

    /**
     * @var \LastCall\Crawler\Module\ModuleProcessor[]
     */
    private $processors = array();

    /**
     * @var \Psr\Log\LoggerInterface
     */
    private $logger;

    public function __construct(ModuleParser $parser, array $processors = array(), LoggerInterface $logger = NULL) {
        $this->parser = $parser;
        $this->processors = $processors;
        $this->logger = $logger ?: new NullLogger();
    }

    public function onCrawlerSuccess(CrawlerResponseEvent $event)
    {
        $modules = $this->parser->parse($event->getDom());
        foreach ($modules as $module) {
            $this->processModule($module);
        }
    }

    private function getProcessors($type)
    {
        return array_filter($this->processors,
          function (ModuleProcessor $processor) use ($type) {
              return in_array($type, $processor->getModuleTypes());
          });
    }

    private function processModule($module)
    {
        $type = $module['type'];

        $processors = $this->getProcessors($type);
        if (empty($processors)) {
            $this->logger->warning(sprintf('Unknown module type: %s', $type));
        }
        else {
            $this->logger->info(sprintf('Processing: %s', $type));
        }
        foreach ($processors as $processor) {
            $processor->process($module);
        }
    }

    /**
     * {@inheritDoc}
     */
    public static function getSubscribedEvents()
    {
        return array(
          Crawler::SUCCESS => 'onCrawlerSuccess',
        );
    }

}