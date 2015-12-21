<?php

use LastCall\Crawler\Configuration\Configuration;
use LastCall\Crawler\Handler\Logging\ExceptionLogger;
use LastCall\Crawler\Handler\Logging\RequestLogger;
use LastCall\Crawler\Handler\Module\ModuleHandler;
use LastCall\Crawler\Module\Parser\XPathParser;
use LastCall\Crawler\Module\Processor\LinkProcessor;
use LastCall\Crawler\Url\Matcher;
use LastCall\Crawler\Url\Normalizer;
use LastCall\Crawler\Url\URLHandler;
use Psr\Log\NullLogger;
use Symfony\Component\Console\Logger\ConsoleLogger;
use Symfony\Component\Console\Output\OutputInterface;

include_once __DIR__ . '/../vendor/autoload.php';


// Create a new configuration, using our website as a base URL.
$config = new Configuration('https://lastcallmedia.com');

// The Matcher determines what URLs are included in the crawl.
// When a new URL is found by the LinkProcessor, it checks with
// the matcher to see if it matches the pattern.  Without
// the matcher, we'd end up crawling the whole internet.
$matcher = new Matcher(['https://lastcallmedia.com']);

// The normalizer cleans up URLs before they are added to the
// queue for processing.  This helps filter out any duplicate
// URLs.
$normalizer = new Normalizer([
    Normalizer::normalizeCase()
]);

// The URLHandler wraps up the Matcher and the Normalizer in a
// nice little package that can be passed around between objects.
$urlHandler = new URLHandler('https://lastcallmedia.com', null, $matcher,
    $normalizer);


// "Modules" are units of content broken out of the response
// by a parser.  They are handed off to a processor to do
// something with.  The ModuleHandler is what coordinates
// the Parsers and the Processors.
$moduleHandler = new ModuleHandler();

// Add the XPathParser, required by the LinkProcessor
$moduleHandler->addParser(new XPathParser());

// Add the LinkProcessor to handle scanning for links
// in the HTML and adding them back to the queue.
$moduleHandler->addProcessor(new LinkProcessor($urlHandler));

// Add the ModuleHandler to the configuration.
// The ModuleHandler will be invoked on every successful
// response.
$config->addSubscriber($moduleHandler);

// Make errors and requests visible by adding loggers.  You can
// use any PSR-3 compatible logger.  Here we're using a null logger,
// but you could replace this with Monolog.
$logger = new NullLogger();
$config->addSubscriber(new RequestLogger($logger));
$config->addSubscriber(new ExceptionLogger($logger));


// Return the Configuration so the CLI runner can run it.
return $config;
