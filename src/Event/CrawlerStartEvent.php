<?php

namespace LastCall\Crawler\Event;

use LastCall\Crawler\Common\HasAdditionalRequests;
use Symfony\Component\EventDispatcher\Event;

/**
 * Wraps data for a crawler start event.
 */
class CrawlerStartEvent extends Event
{
    use HasAdditionalRequests;
}
