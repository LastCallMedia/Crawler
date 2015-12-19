<?php

namespace LastCall\Crawler\Url;

use Psr\Http\Message\UriInterface;

/**
 * An implementation of UriInterface that tracks the transformations
 * it undergoes.
 */
class TraceableUri implements UriInterface
{

    protected $inner;

    /**
     * @var self
     */
    private $previous;

    /**
     * @var self
     */
    private $next;

    private $_string;

    public function __construct(UriInterface $inner)
    {
        $this->inner = $inner;
    }

    public function getPrevious()
    {
        // When you make a change, return a clone of $this with inner set to the new URL.
        // Retain the old URL for historical reasons.
        if ($this->previous) {
            return $this->previous->setNext($this);
        }
    }

    private function setNext(TraceableUri $next)
    {
        $this->next = $next;

        return $this;
    }

    public function getNext()
    {
        if ($this->next) {
            return $this->next;
        }
    }

    /**
     * {@inheritDoc}
     */
    public function getScheme()
    {
        return $this->inner->getScheme();
    }

    public function getAuthority()
    {
        return $this->inner->getAuthority();
    }

    public function getUserInfo()
    {
        return $this->inner->getUserInfo();
    }

    public function getHost()
    {
        return $this->inner->getHost();
    }

    public function getPort()
    {
        return $this->inner->getPort();
    }

    public function getPath()
    {
        return $this->inner->getPath();
    }

    public function getQuery()
    {
        return $this->inner->getQuery();
    }

    public function getFragment()
    {
        return $this->inner->getFragment();
    }

    public function withScheme($scheme)
    {
        return (new static($this->inner->withScheme($scheme)))->setPrevious($this);
    }

    private function setPrevious(TraceableUri $previous)
    {
        $this->previous = $previous;

        return $this;
    }

    public function withUserInfo($user, $password = null)
    {
        return (new static($this->inner->withUserInfo($user,
            $password)))->setPrevious($this);
    }

    public function withHost($host)
    {
        return (new static($this->inner->withHost($host)))->setPrevious($this);
    }

    public function withPort($port)
    {
        return (new static($this->inner->withPort($port)))->setPrevious($this);
    }

    public function withPath($path)
    {
        return (new static($this->inner->withPath($path)))->setPrevious($this);
    }

    public function withQuery($query)
    {
        return (new static($this->inner->withQuery($query)))->setPrevious($this);
    }

    public function withFragment($fragment)
    {
        return (new static($this->inner->withFragment($fragment)))->setPrevious($this);
    }

    public function __toString()
    {
        if (!$this->_string) {
            $this->_string = $this->inner->__toString();
        }

        return $this->_string;
    }
}