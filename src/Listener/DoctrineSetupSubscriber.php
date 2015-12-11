<?php

namespace LastCall\Crawler\Listener;


use Doctrine\Common\Persistence\ManagerRegistry;
use Doctrine\ORM\Tools\SchemaTool;
use LastCall\Crawler\Crawler;
use LastCall\Crawler\CrawlerEvents;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class DoctrineSetupSubscriber implements EventSubscriberInterface
{
    public static function getSubscribedEvents()
    {
        return array(
            CrawlerEvents::TEARDOWN => 'onTeardown',
            CrawlerEvents::SETUP => 'onSetup'
        );
    }

    private $doctrine;

    public function __construct(ManagerRegistry $doctrine)
    {
        $this->doctrine = $doctrine;
    }

    public function onSetup() {
        foreach ($this->doctrine->getManagers() as $om) {
            $tool = new SchemaTool($om);
            $metadatas = $om->getMetadataFactory()->getAllMetadata();
            $tool->createSchema($metadatas);
        }
    }

    public function onTeardown() {
        foreach ($this->doctrine->getManagers() as $om) {
            $tool = new SchemaTool($om);
            $metadatas = $om->getMetadataFactory()->getAllMetadata();
            $tool->dropSchema($metadatas);
        }
    }
}