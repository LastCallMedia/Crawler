<?php

namespace LastCall\Crawler;

/**
 * Defines names of events dispatched by the crawler.
 */
class CrawlerEvents
{
    /**
     * A crawler session is starting.
     */
    const START = 'crawler.start';

    /**
     * Request is sending.
     *
     * The listener method receives a CrawlerRequestEvent instance.
     *
     * @Event
     */
    const SENDING = 'request.sending';

    /**
     * A request has succeeded and a response has been received.
     *
     * The listener method receives a CrawlerResponseEvent instance.
     *
     * @Event
     */
    const SUCCESS = 'request.success';

    /**
     * A request has succeeded and an HTML response has been received.
     *
     * The listener method receives a CrawlerHtmlResponse instance.
     *
     * @Event
     */
    const SUCCESS_HTML = 'request.success.html';

    /**
     * A request has failed and a response has been received.
     *
     * The listener method receives a CrawlerResponseEvent instance.
     *
     * @Event
     */
    const FAILURE = 'request.failure';

    /**
     * A request has failed and an HTML response has been received.
     *
     * The listener method receives a CrawlerHtmlResponse instance.
     *
     * @Event
     */
    const FAILURE_HTML = 'request.success.html';

    /**
     * One or more URIs have been discovered from the response.
     *
     * The listener method receives a CrawlerUrisDiscovered instance.
     *
     * @Event
     */
    const URIS_DISCOVERED = 'crawler.uris_discovered';

    /**
     * Sending or processing a request has resulted in an exception.
     *
     * The listener method receives a CrawlerExceptionEvent instance.
     *
     * @Event
     */
    const EXCEPTION = 'request.exception';

    /**
     * Setup tasks have been requested.
     *
     * @Event
     */
    const SETUP = 'crawler.setup';

    /**
     * Teardown tasks have been requested.
     *
     * @Event
     */
    const TEARDOWN = 'crawler.teardown';

    /**
     * Finish tasks have been requested.
     *
     * @Event
     */
    const FINISH = 'crawler.finish';
}
