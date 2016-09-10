<?php

namespace LastCall\Crawler\Test\Fragment\Processor;

use LastCall\Crawler\Event\CrawlerResponseEvent;
use LastCall\Crawler\Fragment\Parser\CSSSelectorParser;
use LastCall\Crawler\Handler\Fragment\FragmentHandler;
use LastCall\Crawler\Fragment\Parser\XPathParser;
use LastCall\Crawler\Fragment\Processor\FragmentProcessorInterface;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;

trait ProcessesTestFragments
{
    public function fireSuccess(FragmentProcessorInterface $processor, RequestInterface $request, ResponseInterface $response)
    {
        $event = new CrawlerResponseEvent($request, $response);
        $fragment = new FragmentHandler([new XPathParser(), new CSSSelectorParser()], [$processor]);
        $fragment->onSuccess($event);

        return $event;
    }
}
