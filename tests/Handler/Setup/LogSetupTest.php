<?php


namespace LastCall\Crawler\Test\Handler\Setup;


use LastCall\Crawler\CrawlerEvents;
use LastCall\Crawler\Handler\Setup\LogSetup;
use LastCall\Crawler\Test\Handler\HandlerTestTrait;
use Symfony\Component\Filesystem\Filesystem;

class LogSetupTest extends \PHPUnit_Framework_TestCase
{
    use HandlerTestTrait;

    public function testCreatesDir()
    {
        $dir = sys_get_temp_dir() . '/phpunit/crawler-log-dir' . mt_rand(1,
                9999999);
        $handler = new LogSetup($dir);
        $this->invokeEvent($handler, CrawlerEvents::SETUP);
        $this->assertTrue(is_dir($dir));
        rmdir($dir);
    }

    public function testRemovesFiles()
    {
        $dir = sys_get_temp_dir() . '/phpunit/crawler-log-dir' . mt_rand(1,
                9999999);
        $fs = new Filesystem();
        $fs->mkdir($dir);
        $fs->touch($dir . '/test.txt');
        $fs->touch($dir . '/test.log');
        $handler = new LogSetup($dir);
        $this->invokeEvent($handler, CrawlerEvents::TEARDOWN);
        $this->assertTrue(file_exists($dir . '/test.txt'));
        $this->assertFalse(file_exists($dir . '/test.log'));
    }

}