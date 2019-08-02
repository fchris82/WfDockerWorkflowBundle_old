<?php declare(strict_types=1);
/**
 * Created by IntelliJ IDEA.
 * User: chris
 * Date: 2018.12.27.
 * Time: 10:32
 */

namespace Wf\DockerWorkflowBundle\Tests\Environment\MicroParser;

use PHPUnit\Framework\TestCase;
use Symfony\Component\Filesystem\Exception\FileNotFoundException;
use Symfony\Component\Filesystem\Filesystem as SfFilesystem;
use Wf\DockerWorkflowBundle\Environment\MicroParser\ComposerJsonInformationParser;
use Wf\DockerWorkflowBundle\Exception\InvalidComposerVersionNumber;
use Wf\DockerWorkflowBundle\Test\Dummy\Filesystem;

class ComposerJsonInformationParserTest extends TestCase
{
    /**
     * @param string                       $directory
     * @param string                       $infoPath
     * @param string|array|bool|\Exception $result
     *
     * @dataProvider getGets
     */
    public function testGet($directory, $infoPath, $result)
    {
        $workingDirectory = __DIR__ . '/../../Resources/Environment/' . $directory;
        $filesystem = new Filesystem($workingDirectory);
        $parser = new ComposerJsonInformationParser($filesystem);

        if ($result instanceof \Exception) {
            $this->expectException(\get_class($result));
        }

        $response = $parser->get($workingDirectory, $infoPath);
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
            ['env_composer_no_sf', 'name', 'laravel/laravel'],
            ['env_composer_no_sf', 'require.php', '>=7.0.0'],
            ['env_composer_no_sf', 'require.laravel/framework', '5.5.*'],
            ['env_composer_no_sf', 'scripts.post-create-project-cmd', ['@php artisan key:generate', '@php artisan storage:link']],
        ];
    }

    /**
     * @param string           $directory
     * @param string           $infoPath
     * @param mixed|\Exception $result
     *
     * @dataProvider getGets
     */
    public function testHas($directory, $infoPath, $result)
    {
        $workingDirectory = __DIR__ . '/../../Resources/Environment/' . $directory;
        $filesystem = new Filesystem($workingDirectory);
        $parser = new ComposerJsonInformationParser($filesystem);

        if ($result instanceof \Exception) {
            $this->expectException(\get_class($result));
        }

        $response = $parser->has($workingDirectory, $infoPath);
        if (!$result instanceof \Exception) {
            $this->assertEquals(false !== $result, $response);
        }
    }

    /**
     * @param string $version
     * @param string $result
     *
     * @dataProvider getVersions
     */
    public function testReadComposerVersion(string $version, $result)
    {
        $parser = new ComposerJsonInformationParser(new SfFilesystem());

        if ($result instanceof \Exception) {
            $this->expectException(\get_class($result));
        }

        $response = $parser->readComposerVersion($version);
        if (!$result instanceof \Exception) {
            $this->assertEquals($result, $response);
        }
    }

    public function getVersions(): array
    {
        return [
            ['', new InvalidComposerVersionNumber()],
            ['text', new InvalidComposerVersionNumber()],
            ['1', '1'],
            ['1.0', '1.0'],
            ['2.3.4.5', '2.3.4.5'],
            ['v2.3.4.5', '2.3.4.5'],
            ['v2.3.4.5-beta', '2.3.4.5'],
            ['v2.3.4.5-beta-3', '2.3.4.5'],
            ['2.3.*', '2.3'],
            ['>=2.3', '2.3'],
            ['~2.3.5', '2.3.5'],
        ];
    }
}
