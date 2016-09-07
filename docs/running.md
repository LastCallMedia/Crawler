Running the Crawler
===================

Once you have a [configuration](configuration.md), you can start running your crawler.  The crawler exposes 4 subcommands, accessible through the main binary at `bin/crawler`.  These commands are:

*  `setup` - Prepare for crawling (used for creating database schemas, creating log directories, etc).
*  `crawl` - Execute a crawl session
*  `teardown` - Remove any artifacts (used for removing database schemas, tearing down database tables, etc).
*  `reset` - A combination of `setup` and `teardown`.  Used for resetting the workspace between crawler runs.

Example of using the crawl command to run the sample configuration:

```bash
vendor/bin/crawler crawl docs/sample.php
```
