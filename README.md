Static Site Crawler
===================

[![Build Status](https://travis-ci.org/LastCallMedia/Crawler.svg?branch=master)](https://travis-ci.org/LastCallMedia/Crawler)

This is a CLI tool to crawl a website and do processing on the data that's found.  The most common use case would be for scraping content off of an HTML site, but it could also be used to invoke an API and process the data.

Installation
-------

Install the crawler using [Composer](http://getcomposer.org).

```bash
    php composer.phar require lastcall/last-call-crawler
```

You can also add the dependency directly to your project's composer.json:

```json
    {
        "require": {
            "lastcall/last-call-crawler": "~1.0"
        }
    }
```

Documentation can be found here:

* [Configurations](docs/configuration.md)
* [Running](docs/running.md)
* [Handlers](docs/handlers.md)
