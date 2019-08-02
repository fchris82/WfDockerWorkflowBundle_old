<?php declare(strict_types=1);
/**
 * Created by IntelliJ IDEA.
 * User: chris
 * Date: 2018.12.27.
 * Time: 15:23
 */

namespace Wf\DockerWorkflowBundle\Tests\Environment;

use PHPUnit\Framework\TestCase;
use Symfony\Component\Config\Definition\Exception\InvalidConfigurationException;
use Symfony\Component\EventDispatcher\EventDispatcher;
use Wf\DockerWorkflowBundle\Configuration\Configuration;
use Wf\DockerWorkflowBundle\Configuration\RecipeManager;
use Wf\DockerWorkflowBundle\Environment\WfEnvironmentParser;
use Wf\DockerWorkflowBundle\Test\Dummy\Filesystem;

class WfEnvironmentParserTest extends TestCase
{
    /**
     * @param string          $directory
     * @param bool|\Exception $result
     * @param array           $removeFiles
     *
     * @dataProvider getInitTests
     */
    public function testWfIsInitialized(string $directory, $result, array $removeFiles = [])
    {
        $workingDirectory = __DIR__ . '/../Resources/Environment/' . $directory;
        $filesystem = new Filesystem($workingDirectory);
        $configuration = new Configuration(new RecipeManager(), $filesystem, new EventDispatcher());
        $wfParser = new WfEnvironmentParser($configuration, $filesystem);

        foreach ($removeFiles as $file) {
            $filesystem->remove($workingDirectory . '/' . $file);
        }

        if ($result instanceof \Exception) {
            $this->expectException(\get_class($result));
        }

        $response = $wfParser->wfIsInitialized($workingDirectory);
        if (!$result instanceof \Exception) {
            $this->assertEquals($result, $response);
        }
    }

    public function getInitTests(): array
    {
        return [
            ['env_empty', false],
            ['env_wf', true],
            ['env_wf', true, ['.wf.yml']],
            ['env_wf', true, ['.wf.yml.dist']],
        ];
    }

    /**
     * @param string          $directory
     * @param bool|\Exception $result
     * @param array           $removeFiles
     *
     * @dataProvider getConfigurations
     */
    public function testGetWorkflowConfiguration(string $directory, $result, array $removeFiles = [])
    {
        $workingDirectory = __DIR__ . '/../Resources/Environment/' . $directory;
        $filesystem = new Filesystem($workingDirectory);
        $configuration = new Configuration(new RecipeManager(), $filesystem, new EventDispatcher());
        $wfParser = new WfEnvironmentParser($configuration, $filesystem);

        foreach ($removeFiles as $file) {
            $filesystem->remove($workingDirectory . '/' . $file);
        }

        if ($result instanceof \Exception) {
            $this->expectException(\get_class($result));
        }

        $response = $wfParser->getWorkflowConfiguration($workingDirectory);
        if (!$result instanceof \Exception) {
            $this->assertEquals($result, $response);
        }
    }

    public function getConfigurations(): array
    {
        // Full
        $config = [
            'version' => [
                'base' => '2.0.0',
                'wf_minimum_version' => '2.1.1',
            ],
            'name' => 'test',
            'imports' => [
                '.wf.yml.dist',
            ],
            'docker_data_dir' => '%wf.target_directory%/.data',
        ];
        // Only dist
        $config_dist = $config;
        $config_dist['version'] = [
            'base' => '2.0.0',
            'wf_minimum_version' => null,
        ];
        $config_dist['imports'] = [];

        return [
            ['env_empty', new \InvalidArgumentException()],
            ['env_wf', $config],
            ['env_wf', $config_dist, ['.wf.yml']],
            ['env_wf', new InvalidConfigurationException(), ['.wf.yml.dist']], // Missing imported .dist file!
        ];
    }
}
