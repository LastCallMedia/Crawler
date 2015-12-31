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

Out of the box, the base configuration will recursively crawl through the links it finds on the site.  If you want it to do something else, you need to add some [handlers](handlers.md).

[Sample configuration](sample.php)
