<?php


namespace LastCall\Crawler\Test\Configuration\Factory;


use LastCall\Crawler\Configuration\Configuration;
use LastCall\Crawler\Configuration\Factory\PHPFileConfigurationFactory;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Input\InputDefinition;

class PHPFileConfigurationFactoryTest extends \PHPUnit_Framework_TestCase
{
    protected function createInput(array $parameters) {
        $definition = new InputDefinition();
        (new PHPFileConfigurationFactory())->configureInput($definition);
        return new ArrayInput($parameters, $definition);
    }

    /**
     * @expectedException \Exception
     * @expectedExceptionMessage Configuration file foobar.php does not exist.
     */
    public function testLoadInvalidConfigFile() {
        $factory = new PHPFileConfigurationFactory();
        $input = $this->createInput(['filename' => 'foobar.php']);
        $factory->getConfiguration($input);
    }

    /**
     * @expectedException \Exception
     * @expectedExceptionMessage Configuration must implement LastCall\Crawler\Configuration\ConfigurationInterface
     */
    public function testLoadInvalidConfigFileNoReturn() {
        $filename = sys_get_temp_dir() . '/crawler-phpunit-' . mt_rand() . '.php';
        file_put_contents($filename, "<?php\n");
        $factory = new PHPFileConfigurationFactory();
        $input = $this->createInput(['filename' => $filename]);
        $factory->getConfiguration($input);
    }

    public function testLoadConfigFile() {
        $filename = sys_get_temp_dir() . '/crawler-phpunit-' . mt_rand() . '.php';
        file_put_contents($filename, "<?php\nreturn new LastCall\Crawler\Configuration\Configuration('https://lastcallmedia.com');");
        $factory = new PHPFileConfigurationFactory();
        $input = $this->createInput(['filename' => $filename]);
        $config = $factory->getConfiguration($input);
        $this->assertInstanceOf(Configuration::class, $config);
    }
}
