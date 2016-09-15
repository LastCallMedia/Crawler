Handlers
========

Handlers are responsible for processing the responses received by the crawler.  Without them, nothing happens.

Adding Handlers
---------------
Handlers can be added to the configuration like so:

```php
$config = new Configuration();

$config->extend('subscribers', function(array $subscribers) {
    $subscribers['myhandler'] = new MyHandler();
});
```

Packaged Handlers
-----------------
There are some core handlers that are packaged with the crawler and can be used:

Setup:

* [LogSetup](../src/Handler/Setup/LogSetup.php)

Logging:

* [ExceptionLogger](../src/Handler/Logging/ExceptionLogger.php)
* [RequestLogger](../src/Handler/Logging/RequestLogger.php)

Discovery:

* [LinkDiscoverer](../src/Handler/Discovery/LinkDiscoverer.php)
* [AssetDiscoverer](../src/Handler/Discovery/AssetDiscoverer.php)
* [RedirectDiscoverer](../src/Handler/Discovery/RedirectDiscoverer.php)

Uri:

* [UriRecursor](../src/Handler/Uri/UriRecursor.php)

Html:

* [HtmlRedispatcher](../src/Handler/HtmlRedispatcher.php)


Creating Handlers
-----------------
Handlers are just `Symfony\Component\EventDispatcher\EventSubscriberInterface` objects that react when events happen in the crawler.  The events you can listen for are:

* **CrawlerEvents::START** - (`CrawlerStartEvent $event`) - The crawler session is starting and the configuration should be prepared by adding any initial requests to the queue.
* **CrawlerEvents::SENDING** (`CrawlerRequestEvent $event`)- A request is about to be sent.
* **CrawlerEvents::SUCCESS** (`CrawlerResponseEvent $event`) - A request has been sent and a response has been received.  The response has been deemed "successful" by the client.
* **CrawlerEvents::SUCCESS_HTML** (`CrawlerHtmlResponseEvent $event`) - A successful response that has been determined to contain HTML.
* **CrawlerEvents::FAILURE** (`CrawlerResponseEvent $event`) - A request has been sent and a response has been received.  The response has been deemed "failed" by the client.
* **CrawlerEvents::FAILURE_HTML** (`CrawlerHtmlResponseEvent $event`) - A failed response that has been determined to contain HTML.
* **CrawlerEvents::EXCEPTION** (`CrawlerExceptionEvent $event`) - An exception has occurred during crawling.  This could be before or after the response was received.
* **CrawlerEvents::URIS_DISCOVERED** (`CrawlerUrisDiscoveredEvent $event`) - Uris have been discovered during the processing of the response.
* **CrawlerEvents::FINISH** - The session is ending.  Handlers have a chance to perform any cleanup or reporting tasks.
* **CrawlerEvents::SETUP** - The user has requested that setup tasks are run.
* **CrawlerEvents::TEARDOWN** - The user has requested that teardown tasks are run.
