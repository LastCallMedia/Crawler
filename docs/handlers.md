Handlers
========

Handlers are responsible for processing the responses received by the crawler.  Without them, nothing happens.

Adding Handlers
---------------
Handlers can be added to the simple configuration like so:

```php
$config = new Configuration();
$config->addSubscriber(new MyHandler());
```

If you are using a subclass configuration, you'll just need to add them when your class is initialized:

```php
class MyConfig extends AbstractConfiguration {

    public function __construct() {
        ... 
        $this->subscribers = [
            new MyHandler()
        ];
        ...
    }
}
```

Packaged Handlers
-----------------
There are some core handlers that are packaged with the crawler and can be used:



Logging:

* [ExceptionLogger](../src/Handler/Logging/ExceptionLogger.php)
* [RequestLogger](../src/Handler/Logging/ExceptionLogger.php)

Modules:

* [FragmentHandler](../src/Handler/Module/FragmentHandler.php)

Discovery:

* [DenormalizedUrlDiscoverer](../src/Handler/Discovery/DenormalizedUrlDiscoverer.php)
* [RedirectDiscoverer](../src/Handler/Discovery/RedirectDiscoverer.php)

Reporting

* [CrawlerStatusReporter](../src/Handler/Reporting/CrawlerStatusReporter.php)

The FragmentHandler also allows for additional processing to be done on subtrees of a page.  For example, the [LinkProcessor](../src/Module/Processor/LinkProcessor.php) hooks into the FragmentHandler to parse and re-add links.

Creating Handlers
-----------------
Handlers are just `Symfony\Component\EventDispatcher\EventSubscriberInterface` objects that react when events happen in the crawler.  The events you can listen for are:

* **CrawlerEvents::SENDING** (`CrawlerEvent $event`)- A request is about to be sent.
* **CrawlerEvents::SUCCESS** (`CrawlerResponseEvent $event`) - A request has been sent and a response has been received.  The response has been deemed "successful" by the client.
* **CrawlerEvents::FAILURE** (`CrawlerResponseEvent $event`) - A request has been sent and a response has been received.  The response has been deemed "failed" by the client.
* **CrawlerEvents::EXCEPTION** (`CrawlerExceptionEvent $event`) - An exception has occurred during crawling.  This could be before or after the response was received.
* **CrawlerEvents::SETUP** - The user has requested that setup tasks are run.
* **CrawlerEvents::TEARDOWN** - The user has requested that teardown tasks are run.
