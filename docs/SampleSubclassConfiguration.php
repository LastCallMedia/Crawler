<?php

namespace {

    use GuzzleHttp\Client;
    use LastCall\Crawler\Configuration\AbstractConfiguration;
    use LastCall\Crawler\Handler\Logging\ExceptionLogger;
    use LastCall\Crawler\Handler\Logging\RequestLogger;
    use LastCall\Crawler\Handler\Module\ModuleHandler;
    use LastCall\Crawler\Module\Parser\XPathParser;
    use LastCall\Crawler\Module\Processor\LinkProcessor;
    use LastCall\Crawler\Queue\ArrayRequestQueue;
    use LastCall\Crawler\Url\Matcher;
    use LastCall\Crawler\Url\Normalizer;
    use LastCall\Crawler\Url\URLHandler;
    use Symfony\Component\Console\Logger\ConsoleLogger;
    use Symfony\Component\Console\Output\OutputInterface;

    class SampleSubclassConfiguration extends AbstractConfiguration
    {

        public function __construct()
        {
            $this->queue = new ArrayRequestQueue();
            $this->subscribers = $this->createSubscribers();
            $this->baseUrl = 'https://lastcallmedia.com';
            $this->client = new Client(['allow_redirects' => false]);
            $this->urlHandler = $this->createUrlHandler();
        }

        public function attachOutput(OutputInterface $output)
        {
            $consoleLogger = new ConsoleLogger($output);
            $this->subscribers[] = new ExceptionLogger($consoleLogger);
            $this->subscribers[] = new RequestLogger($consoleLogger);
        }

        private function createUrlHandler()
        {
            $matcher = new Matcher(['https://lastcallmedia.com']);
            $normalizer = new Normalizer([
                Normalizer::normalizeCase('lower')
            ]);

            return new URLHandler('https://lastcallmedia.com', null, $matcher,
                $normalizer);
        }

        private function createSubscribers()
        {
            $moduleHandler = new ModuleHandler();
            $moduleHandler->addParser(new XPathParser());
            $moduleHandler->addProcessor(new LinkProcessor());

            return [$moduleHandler];
        }
    }

    // Returning here for simplicy, but the actual instance creation
    // should happen in a bare PHP file that you include from the command line.
    $config = new SampleSubclassConfiguration();

    return $config;
}

