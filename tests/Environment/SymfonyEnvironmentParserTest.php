<?php declare(strict_types=1);
/**
 * Created by IntelliJ IDEA.
 * User: chris
 * Date: 2018.12.27.
 * Time: 10:26
 */

namespace Wf\DockerWorkflowBundle\Tests\Environment;

use Mockery as m;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Console\Question\ChoiceQuestion;
use Symfony\Component\Filesystem\Exception\FileNotFoundException;
use Wf\DockerWorkflowBundle\Environment\IoManager;
use Wf\DockerWorkflowBundle\Environment\MicroParser\ComposerInstalledVersionParser;
use Wf\DockerWorkflowBundle\Environment\SymfonyEnvironmentParser;
use Wf\DockerWorkflowBundle\Exception\InvalidComposerVersionNumber;
use Wf\DockerWorkflowBundle\Test\Dummy\Filesystem;

class SymfonyEnvironmentParserTest extends TestCase
{
    public function tearDown(): void
    {
        m::close();
    }

    /**
     * @param string $directory
     * @param bool   $result
     * @param bool   $removeLock
     *
     * @dataProvider getVersions
     */
    public function testGetSymfonyVersion($directory, $result, $removeLock = false)
    {
        $ioManager = m::mock(IoManager::class);
        $workingDirectory = __DIR__ . '/../Resources/Environment/' . $directory;
        $filesystem = new Filesystem($workingDirectory);
        $composerParser = new ComposerInstalledVersionParser($filesystem);
        $sfParser = new SymfonyEnvironmentParser($ioManager, $composerParser);

        if ($removeLock) {
            $filesystem->remove($workingDirectory . '/composer.lock');
        }

        if ($result instanceof \Exception) {
            $this->expectException(\get_class($result));
        }

        $response = $sfParser->getSymfonyVersion($workingDirectory);
        if (!$result instanceof \Exception) {
            $this->assertEquals($result, $response);
        }
    }

    public function getVersions(): array
    {
        return [
            ['env_empty', new FileNotFoundException()],
            ['env_no_composer', new FileNotFoundException()],
            ['env_composer_no_sf', false],
            ['env_composer_no_sf_only_json', false],
            ['env_composer_sf2', '2.8.14'],
            ['env_composer_sf3', '3.4.8'],
            ['env_composer_sf4', '4.1.9'],
            ['env_composer_sf2', false, true],
            ['env_composer_sf3', '3.4', true],
            ['env_composer_sf4', '4.1', true],
            ['env_composer_sf1_invalid', '1.4.0'],
            ['env_composer_sf_invalid_version', new InvalidComposerVersionNumber()],
        ];
    }

    /**
     * @param string           $directory
     * @param mixed|\Exception $result
     *
     * @dataProvider getBinDirs
     */
    public function testReadSymfonyBinDir($directory, $result)
    {
        $ioManager = m::mock(IoManager::class);
        $workingDirectory = __DIR__ . '/../Resources/Environment/' . $directory;
        $filesystem = new Filesystem($workingDirectory);
        $composerParser = new ComposerInstalledVersionParser($filesystem);
        $sfParser = new SymfonyEnvironmentParser($ioManager, $composerParser);

        if ($result instanceof \Exception) {
            $this->expectException(\get_class($result));
        }

        $response = $sfParser->readSymfonyBinDir($workingDirectory);
        if (!$result instanceof \Exception) {
            $this->assertEquals($result, $response);
        }
    }

    public function getBinDirs(): array
    {
        return [
            ['env_empty', new FileNotFoundException()],
            ['env_no_composer', new FileNotFoundException()],
            ['env_composer_no_sf', null],
            ['env_composer_no_sf_only_json', null],
            ['env_composer_sf2', 'bin'],
            ['env_composer_sf3', 'bin'],
            ['env_composer_sf4', null],
        ];
    }

    /**
     * @param string           $directory
     * @param array|\Exception $result
     * @param int|null         $selectedIndex
     *
     * @dataProvider getVariables
     */
    public function testGetSymfonyEnvironmentVariables($directory, $result, $selectedIndex = null)
    {
        $ioManager = m::mock(IoManager::class);
        if (null !== $selectedIndex) {
            $ioManager
                ->shouldReceive('ask')
                ->once()
                ->andReturnUsing(function (ChoiceQuestion $question) use ($selectedIndex) {
                    $askChoices = $question->getChoices();

                    return $askChoices[$selectedIndex];
                })
            ;
        }
        $workingDirectory = __DIR__ . '/../Resources/Environment/' . $directory;
        $filesystem = new Filesystem($workingDirectory);
        $composerParser = new ComposerInstalledVersionParser($filesystem);
        $sfParser = new SymfonyEnvironmentParser($ioManager, $composerParser);

        if ($result instanceof \Exception) {
            $this->expectException(\get_class($result));
        }

        $response = $sfParser->getSymfonyEnvironmentVariables($workingDirectory);
        if (!$result instanceof \Exception) {
            $this->assertEquals($result, $response);
        }
    }

    public function getVariables(): array
    {
        $variables = [
            '2' => [
                SymfonyEnvironmentParser::VARIABLE_VERSION       => 2,
                SymfonyEnvironmentParser::VARIABLE_CONSOLE_CMD   => 'app/console',
                SymfonyEnvironmentParser::VARIABLE_BIN_DIR       => 'bin',
                SymfonyEnvironmentParser::VARIABLE_SHARED_DIRS   => 'app/cache app/logs',
                SymfonyEnvironmentParser::VARIABLE_WEB_DIRECTORY => 'web',
                SymfonyEnvironmentParser::VARIABLE_INDEX_FILE    => 'app.php',
            ],
            '3_1' => [
                SymfonyEnvironmentParser::VARIABLE_VERSION       => 3,
                SymfonyEnvironmentParser::VARIABLE_CONSOLE_CMD   => 'bin/console',
                SymfonyEnvironmentParser::VARIABLE_BIN_DIR       => 'vendor/bin', // default
                SymfonyEnvironmentParser::VARIABLE_SHARED_DIRS   => 'var',
                SymfonyEnvironmentParser::VARIABLE_WEB_DIRECTORY => 'web',
                SymfonyEnvironmentParser::VARIABLE_INDEX_FILE    => 'app.php',
            ],
            '3_2' => [
                SymfonyEnvironmentParser::VARIABLE_VERSION       => 3,
                SymfonyEnvironmentParser::VARIABLE_CONSOLE_CMD   => 'bin/console',
                SymfonyEnvironmentParser::VARIABLE_BIN_DIR       => 'bin', // It was read from composer.json
                SymfonyEnvironmentParser::VARIABLE_SHARED_DIRS   => 'var',
                SymfonyEnvironmentParser::VARIABLE_WEB_DIRECTORY => 'web',
                SymfonyEnvironmentParser::VARIABLE_INDEX_FILE    => 'app.php',
            ],
            '4' => [
                SymfonyEnvironmentParser::VARIABLE_VERSION       => 4,
                SymfonyEnvironmentParser::VARIABLE_CONSOLE_CMD   => 'bin/console',
                SymfonyEnvironmentParser::VARIABLE_BIN_DIR       => 'vendor/bin',
                SymfonyEnvironmentParser::VARIABLE_SHARED_DIRS   => 'var',
                SymfonyEnvironmentParser::VARIABLE_WEB_DIRECTORY => 'public',
                SymfonyEnvironmentParser::VARIABLE_INDEX_FILE    => 'index.php',
            ],
        ];

        return [
            ['env_empty', new FileNotFoundException()],
            ['env_no_composer', new FileNotFoundException()],
            ['env_composer_no_sf', $variables['2'], 2],
            ['env_composer_no_sf_only_json', $variables['3_1'], 1],
            ['env_composer_sf2', $variables['2']],
            ['env_composer_sf3', $variables['3_2']],
            ['env_composer_sf4', $variables['4']],
            ['env_composer_sf1_invalid', new \InvalidArgumentException()],
            ['env_composer_sf_invalid_version', $variables['4'], 0],
        ];
    }
}
