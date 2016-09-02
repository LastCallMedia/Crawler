<?php


namespace LastCall\Crawler\Event;


use LastCall\Crawler\Session\SessionInterface;
use Symfony\Component\EventDispatcher\Event;

class CrawlerStartEvent extends Event {

    private $session;

    /**
     * CrawlerStartEvent constructor.
     *
     * @param \LastCall\Crawler\Session\SessionInterface $session
     */
    public function __construct(SessionInterface $session) {
        $this->session = $session;
    }

    /**
     * Get the session that is starting.
     *
     * @return \LastCall\Crawler\Session\SessionInterface
     */
    public function getSession() {
        return $this->session;
    }

}