<?php

namespace LastCall\Crawler\Test\Listener;


use LastCall\Crawler\Listener\LogSetupSubscriber;
use Symfony\Component\Filesystem\Filesystem;

class LogClearingSubscriberTest extends \PHPUnit_Framework_TestCase
{
    public function testCreatesDir() {
        $dir = sys_get_temp_dir() . '/phpunit/crawler-log-dir' . mt_rand(1, 9999999);
        $sub = new LogSetupSubscriber($dir);
        $sub->onSetup();
        $this->assertTrue(is_dir($dir));
        rmdir($dir);
    }

    public function testRemovesFiles() {
        $dir = sys_get_temp_dir() . '/phpunit/crawler-log-dir' . mt_rand(1, 9999999);
        $fs = new Filesystem();
        $fs->mkdir($dir);
        $fs->touch($dir . '/test.txt');
        $fs->touch($dir . '/test.log');
        $sub = new LogSetupSubscriber($dir);
        $sub->onTeardown();
        $this->assertTrue(file_exists($dir . '/test.txt'));
        $this->assertFalse(file_exists($dir . '/test.log'));
    }

}