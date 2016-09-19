Handlers
========

Handlers are responsible for processing the responses received by the crawler.  Without them, nothing happens.

Adding Handlers
---------------
Handlers are added to the configuration by making them available on the container, then specifying that they should be used.  In the base configuration, there are three types of supported handlers: loggers, discoverers, and recursors.  Loggers log messages about events as they are fired.  Discoverers search the response for new URIs.  Recursors take discoverered URIs and add them back into the queue as requests if they match certain conditions.

```php
$config = new Configuration();

// Make a new logger handler available by adding it under the "logger" prefix.
$config['logger.myawesomelogger'] = function() use ($config) {
    return new MyAwesomeLogger($config['logger']);
}

// Use the logger in the configuration.  The name here is whatever follows the word "logger" in the service ID.
$config['loggers'][] = 'myawesomelogger';
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
* [RedirectDiscoverer](../src/Handler/Discovery/RedirectDiscoverer.php)
* [ImageDiscoverer](../src/Handler/Discovery/ImageDiscoverer.php)
* [ScriptDiscoverer](../src/Handler/Discovery/ScriptDiscoverer.php)
* [StylesheetDiscoverer](../src/Handler/Discovery/StylesheetDiscoverer.php)

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
