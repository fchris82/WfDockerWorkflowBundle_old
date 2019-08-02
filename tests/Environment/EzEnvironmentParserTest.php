<?php declare(strict_types=1);
/**
 * Created by IntelliJ IDEA.
 * User: chris
 * Date: 2018.12.27.
 * Time: 14:58
 */

namespace Wf\DockerWorkflowBundle\Tests\Environment;

use Mockery as m;
use PHPUnit\Framework\TestCase;
use Wf\DockerWorkflowBundle\Environment\EzEnvironmentParser;
use Wf\DockerWorkflowBundle\Environment\IoManager;
use Wf\DockerWorkflowBundle\Environment\MicroParser\ComposerInstalledVersionParser;
use Wf\DockerWorkflowBundle\Test\Dummy\Filesystem;

class EzEnvironmentParserTest extends TestCase
{
    /**
     * @param string          $directory
     * @param bool|\Exception $result
     * @param array           $removeFiles
     *
     * @dataProvider getProjects
     */
    public function testIsEzProject($directory, $result, $removeFiles = [])
    {
        $ioManager = m::mock(IoManager::class);
        $workingDirectory = __DIR__ . '/../Resources/Environment/' . $directory;
        $filesystem = new Filesystem($workingDirectory);
        $composerParser = new ComposerInstalledVersionParser($filesystem);
        $ezParser = new EzEnvironmentParser($ioManager, $composerParser);

        foreach ($removeFiles as $file) {
            $filesystem->remove($workingDirectory . '/' . $file);
        }

        if ($result instanceof \Exception) {
            $this->expectException(\get_class($result));
        }

        $response = $ezParser->isEzProject($workingDirectory);
        if (!$result instanceof \Exception) {
            $this->assertEquals($result, $response);
        }
    }

    public function getProjects(): array
    {
        return [
            ['env_empty', false],
            ['env_no_composer', false],
            ['env_composer_no_sf', false],
            ['env_composer_no_sf_only_json', false],
            ['env_composer_sf2', false],
            ['env_composer_sf3', false],
            ['env_composer_sf4', false],
            ['env_composer_ez1', true],
            ['env_composer_ez1', true, ['.ez.yml']],
            ['env_composer_ez2', true],
            ['env_composer_ez_invalid_version', true],
        ];
    }

    // @todo
//    public function testGetSymfonyEnvironmentVariables()
//    {
//
//    }
}
