<?php


namespace LastCall\Crawler\Test\Configuration\Loader;


use LastCall\Crawler\Configuration\ConfigurationInterface;
use LastCall\Crawler\Configuration\Loader\PHPConfigurationLoader;

class PHPConfigurationLoaderTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @expectedException \Exception
     * @expectedExceptionMessage Configuration file foobar.php does not exist.
     */
    public function testLoadNonexistentFile() {
        $loader = new PHPConfigurationLoader();
        $loader->loadFile('foobar.php');
    }

    /**
     * @expectedException \Exception
     * @expectedExceptionMessage README.md does not have a .php file extension.
     */
    public function testLoadNonPHPfile() {
        $loader = new PHPConfigurationLoader();
        $loader->loadFile(__DIR__ .'/../../../README.md');
    }

    /**
     * @expectedException \Exception
     * @expectedExceptionMessage Configuration must implement LastCall\Crawler\Configuration\ConfigurationInterface
     */
    public function testLoadNoreturnFile() {
        $filename = sys_get_temp_dir().'/crawler-phpunit-'.mt_rand().'.php';
        file_put_contents($filename, "<?php\n");
        $loader = new PHPConfigurationLoader();
        $loader->loadFile($filename);
    }

    public function testLoadFile() {
        $loader = new PHPConfigurationLoader();
        $config = $loader->loadFile(__DIR__ .'/../../../docs/sample.php');
        $this->assertInstanceOf('LastCall\Crawler\Configuration\ConfigurationInterface', $config);
    }

}
