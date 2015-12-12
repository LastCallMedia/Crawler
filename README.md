Static Site Crawler
===================

[![Build Status](https://travis-ci.org/LastCallMedia/Crawler.svg?branch=master)](https://travis-ci.org/LastCallMedia/Crawler)

This is a CLI tool to crawl a website and do processing on the data that's found.  The most common use case would be for scraping content off of an HTML site, but it could also be used to invoke an API and process the data.

This tool exposes 3 subcommands:

*  `setup` - Prepare for crawling (used for creating database schemas, creating log directories, etc).
*  `crawl` - Execute a crawl session
*  `teardown` - Remove any artifacts (used for removing database schemas, tearing down database tables, etc).
*  `reset` - A combination of `setup` and `teardown`.  Used for resetting the workspace between crawler runs.

All commands must be run on a given configuration.  A configuration is an instance of the `LastCall\Crawler\Configuration\ConfigurationInterface`.  Configurations contain the following things that are used by the crawler:

* A Base URL (string)
* A Guzzle HTTP Client instance (`GuzzleHttp\Client`)
* A URL Handler (`LastCall\Crawler\Url\UrlHandler`)
* A RequestQueue Driver (`LastCall\Crawler\RequestQueue\Driver\DriverInterface`)
* Event listeners (PHP callables that respond to specific crawler events)
* Event Subscribers (`Symfony\Component\EventDispatcher\EventSubscriberInterface`)

When a configuration is run through the crawler, an HTTP request is made to the Base URL.  When the response is received, it is passed to an Event Dispatcher for processing.  The Event Dispatcher hands the event off to the configuration's listeners and subscribers. The following events are fired by the Crawler:

* CrawlerEvents::SENDING - A request is about to be sent.
* CrawlerEvents::SUCCESS - A request has been sent and a response has been received.  The response has been deemed "successful" by the client.
* CrawlerEvents::FAILURE - A request has been sent and a response has been received.  The response has been deemed "failed" by the client.
* CrawlerEvents::EXCEPTION - An exception has occurred during crawling.  This could be before or after the response was received.
* CrawlerEvents::SETUP - The user has requested that setup tasks are run.
* CrawlerEvents::TEARDOWN - The user has requested that teardown tasks are run.

During crawling, Listeners/Subscribers are responsible for all aspects of the Crawler's behavior.  The crawler does not perform any HTML parsing or link checking on it's own.  In order to keep the crawler running beyond the intial request to the base URL, you'll want to add some subscribers to refill the queue.

