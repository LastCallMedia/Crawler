<?php


namespace LastCall\Crawler\Handler\Module;


use LastCall\Crawler\Common\SetupTeardownInterface;
use LastCall\Crawler\CrawlerEvents;
use LastCall\Crawler\Handler\CrawlerHandlerInterface;
use LastCall\Crawler\Event\CrawlerResponseEvent;
use LastCall\Crawler\Module\ModuleParser;
use LastCall\Crawler\Module\ModuleProcessor;
use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;

class DomModule implements CrawlerHandlerInterface
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

    public function __construct(
        ModuleParser $parser,
        array $processors = array(),
        LoggerInterface $logger = null
    ) {
        $this->parser = $parser;
        $this->processors = $processors;
        $this->logger = $logger ?: new NullLogger();
    }

    public function onSuccess(CrawlerResponseEvent $event)
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
        } else {
            $this->logger->info(sprintf('Processing: %s', $type));
        }
        foreach ($processors as $processor) {
            $processor->process($module);
        }
    }

    public function onSetup() {
        foreach($this->getSetupTeardownProcessors() as $processor) {
            $processor->onSetup();
        }
    }

    public function onTeardown() {
        foreach($this->getSetupTeardownProcessors() as $processor) {
            $processor->onTeardown();
        }
    }

    protected function getSetupTeardownProcessors() {
        return array_filter($this->processors, function(ModuleProcessor $processor) {
            return $processor instanceof SetupTeardownInterface;
        });
    }

    /**
     * {@inheritDoc}
     */
    public static function getSubscribedEvents()
    {
        return array(
            CrawlerEvents::SETUP => 'onSetup',
            CrawlerEvents::SUCCESS => 'onSuccess',
            CrawlerEvents::TEARDOWN => 'onTeardown',
        );
    }

}