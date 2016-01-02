Configuration
=============

A configuration describes what you want to crawl, and what you want to do with the responses that are received.

Creating a Configuration
------------------------
Creating a new configuration is easy:
```php
# myconfig.php
use LastCall\Crawler\Configuration\Configuration;

return new Configuration('http://url.for/my/site);
```

Configurations are [Pimple](http://pimple.sensiolabs.org/) dependency injection containers.  You can use array syntax to extend, redefine, or extend services on the configuration.  See the [Pimple docs](http://pimple.sensiolabs.org/) for more information on how to use the container. 

[Sample configuration](sample.php)

Container Parameters
--------------------
The following parameters are simple values.

#### base_url 

`string` - The base URL is a string representing the URL you want to crawl.  It will be set when the container is created.

There is no default value for the base_url.

#### html_extensions 

`string[]` - An array containing the file extensions we assume contain HTML content.

[Default html_extensions](../src/Configuration/ServiceProvider/MatcherServiceProvider.php)

Container Services
------------------
The following services are registered with the container and can be replaced or extended.

#### matcher 

`LastCall\Crawler\Uri\MatcherInterface` - The matcher is used to check whether URIs discovered during processing responses should be included in the session.

[Default matcher](../src/Configuration/ServiceProvider/MatcherServiceProvider.php)


#### normalizer 

`LastCall\Crawler\Uri\NormalizerInterface` - The normalizer is used to "fix" URIs by applying some standard formatting rules.  This helps prevent duplicate URIs from being added.  For example, if the crawler discovers a link to http://GOOGLE.com and a link to http://google.com, the default normalizer will lowercase the domain name, and these links will be treated as equivalent.

[Default normalizer](../src/Configuration/ServiceProvider/NormalizerServiceProvider.php)

#### normalizations
`array` - An array of normalizations that are performed by the normalizer.

[Default normalizations](../src/Configuration/ServiceProvider/NormalizerServiceProvider.php)

#### queue 

`LastCall\Crawler\Queue\RequestQueueInterface` - The queue is where requests are stored.  Initially, the queue only contains a request to the baseUrl, and the queue is filled by subscribers processing the page. 

[The default queue](../src/Configuration/ServiceProvider/QueueServiceProvider.php)


#### logger 

`PSR\Log\LoggerInterface` - A PSR-3 compatible logger instance that will be used for logging request/response events, including exceptions during processing.

[Default logger](../src/Configuration/ServiceProvider/LoggerServiceProvider.php)

#### doctrine 

`Doctrine\DBAL\Connection` - A Doctrine connection object.  If the `doctrine` service exists on the container, it will be used for the queue backend.  This is optional, but highly recommended, as the default array backend uses a lot of memory when it has to store many requests.

There is no default doctrine definition.




