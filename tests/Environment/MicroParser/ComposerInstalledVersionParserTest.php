<?php declare(strict_types=1);
/**
 * Created by IntelliJ IDEA.
 * User: chris
 * Date: 2018.12.27.
 * Time: 12:37
 */

namespace Wf\DockerWorkflowBundle\Tests\Environment\MicroParser;

use PHPUnit\Framework\TestCase;
use Symfony\Component\Filesystem\Exception\FileNotFoundException;
use Wf\DockerWorkflowBundle\Environment\MicroParser\ComposerInstalledVersionParser;
use Wf\DockerWorkflowBundle\Test\Dummy\Filesystem;

class ComposerInstalledVersionParserTest extends TestCase
{
    /**
     * @param string            $directory
     * @param string            $packageName
     * @param string|\Exception $result
     *
     * @dataProvider getGets
     */
    public function testGet($directory, $packageName, $result)
    {
        $workingDirectory = __DIR__ . '/../../Resources/Environment/' . $directory;
        $filesystem = new Filesystem($workingDirectory);
        $parser = new ComposerInstalledVersionParser($filesystem);

        if ($result instanceof \Exception) {
            $this->expectException(\get_class($result));
        }

        $response = $parser->get($workingDirectory, $packageName);
        if (!$result instanceof \Exception) {
            $this->assertEquals($result, $response);
        }
    }

    public function getGets(): array
    {
        return [
            ['env_empty', '', new FileNotFoundException()],
            ['env_no_composer', '', new FileNotFoundException()],
            ['env_composer_no_sf', '', false],
            ['env_composer_no_sf', 'no_installed', false],
            ['env_composer_no_sf', 'sebastian/version', '2.0.1'],
            ['env_composer_no_sf', 'jakub-onderka/php-console-highlighter', '0.3.2'],
            // There isn't in composer.json
            ['env_composer_no_sf_only_json', 'jakub-onderka/php-console-highlighter', false],
            // There is in require
            ['env_composer_no_sf_only_json', 'laravel/framework', '5.5'],
            // There is in require-dev
            ['env_composer_no_sf_only_json', 'mockery/mockery', '0.9'],
        ];
    }

    /**
     * @param string           $directory
     * @param string           $infoPath
     * @param mixed|\Exception $result
     *
     * @dataProvider getReads
     */
    public function testRead($directory, $infoPath, $result)
    {
        $workingDirectory = __DIR__ . '/../../Resources/Environment/' . $directory;
        $filesystem = new Filesystem($workingDirectory);
        $parser = new ComposerInstalledVersionParser($filesystem);

        if ($result instanceof \Exception) {
            $this->expectException(\get_class($result));
        }

        $response = $parser->read($workingDirectory, $infoPath);
        if (!$result instanceof \Exception) {
            $this->assertEquals($result, $response);
        }
    }

    public function getReads(): array
    {
        return [
            ['env_empty', '', new FileNotFoundException()],
            ['env_no_composer', '', new FileNotFoundException()],
            ['env_composer_sf2', '', false],
            ['env_composer_sf2', 'no_installed', false],
            ['env_composer_sf2', 'platform-overrides.php', '5.5.9'],
            ['env_composer_sf2', 'autoload.classmap.0', 'app/AppKernel.php'],
        ];
    }
}
