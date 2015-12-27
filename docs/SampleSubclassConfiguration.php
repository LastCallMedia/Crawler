<?php

namespace {

    use LastCall\Crawler\Configuration\Configuration;
    use LastCall\Crawler\Uri\Normalizer;
    use Psr\Log\NullLogger;
    use Symfony\Component\Console\Logger\ConsoleLogger;
    use Symfony\Component\Console\Output\OutputInterface;


    class SampleSubclassConfiguration extends Configuration
    {

        public function __construct()
        {
            parent::__construct('https://lastcallmedia.com');

            // Add some normalizers to clean up URLs.
            $this['normalizers'] = [
                Normalizer::normalizeCase(),
                Normalizer::dropFragment()
            ];

            // Add a logger.  Normally, we'd use something like Monolog.
            $this['logger'] = function() {
                return new NullLogger();
            };

            // Add an event subscriber.
            $this->extend('subscribers', function($subscribers) {
                $subscribers['mysubscriber'] = new MySubscriber();
                return $subscribers;
            });
        }
    }

    // Returning here for simplicy, but the actual instance creation
    // should happen in a bare PHP file that you include from the command line.
    $config = new SampleSubclassConfiguration();

    return $config;
}

