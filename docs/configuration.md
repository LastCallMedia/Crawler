Configuration
=============

What is a Configuration
-----------------------
An instance of `LastCall\Crawler\Configuration\ConfigurationInterface` that lives in a .php file and is included by the command line runner.  It specifies what to crawl, and what to do with the responses that are received.  You can either start with the `LastCall\Crawler\Configuration\Configuration` class and build everything up in a single PHP file, or you can subclass `LastCall\Crawler\Configuration\AbstractConfiguration` and build it in a PHP class.  We recommend starting from the AbstractConfiguration, as it will make your configuration easier to test in the future.

Configurations have a few special properties that you need to know about.  These are going to be the same whether you're subclassing or using the Configuration directly, but for brevity's sake, we'll assume you're building up a `Configuration` object in a single PHP file.

Creating a Configuration
------------------------
You'll need to start out by constructing a new configuration:
```php
use LastCall\Crawler\Configuration\Configuration;

$config = new Configuration();
```

If you're crawling an HTML site, you'll need to add a Processor to collect links and add them back into the queue.  This snippet creates a new `FragmentHandler`, and adds a `Parser` and a `Processor`.  Modules are just units of content that are broken out of the original response.  Here, the `LinkProcessor` uses the `XPathParser` to break link tags out of the content.  The `LinkProcessor` knows how to add the discovered URLs back into the queue for processing.

```php
use LastCall\Crawler\Handler\Module\FragmentHandler;
use LastCall\Crawler\Module\Parser\XPathParser;
use LastCall\Crawler\Module\Processor\LinkProcessor;

include_once __DIR__ .'/vendor/autoload.php';

$moduleHandler = new FragmentHandler();
$moduleHandler->addParser(new XPathParser());
$moduleHandler->addProcessor(new LinkProcessor());

```

That's all you need for the most simple crawler configuration.  Make sure to return your configuration at the end of your PHP file:

```php
return $config;
```

[Sample configuration using `LastCall\Crawler\Configuration\Configuration`](sample.php)

[Sample configuration using `LastCall\Crawler\Configuration\AbstractConfiguration`](SampleSubclassConfiguration.php) 