<?php

namespace LastCall\Crawler;


class CrawlerEvents
{

    const SENDING = 'request.sending';
    const SUCCESS = 'request.success';
    const FAILURE = 'request.failure';
    const EXCEPTION = 'request.exception';
    const SETUP = 'crawler.setup';
    const TEARDOWN = 'crawler.teardown';

}