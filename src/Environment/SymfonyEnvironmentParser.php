<?php declare(strict_types=1);
/**
 * Created by IntelliJ IDEA.
 * User: chris
 * Date: 2018.11.30.
 * Time: 12:34
 */

namespace Wf\DockerWorkflowBundle\Environment;

use Symfony\Component\Console\Question\ChoiceQuestion;
use Wf\DockerWorkflowBundle\Environment\MicroParser\ComposerInstalledVersionParser;
use Wf\DockerWorkflowBundle\Exception\InvalidComposerVersionNumber;

class SymfonyEnvironmentParser
{
    const VARIABLE_VERSION = 'sf_version';
    const VARIABLE_CONSOLE_CMD = 'sf_console_cmd';
    const VARIABLE_BIN_DIR = 'sf_bin_dir';
    const VARIABLE_SHARED_DIRS = 'shared_dirs';
    const VARIABLE_WEB_DIRECTORY = 'web_directory';
    const VARIABLE_INDEX_FILE = 'index_file';

    /**
     * @var IoManager
     */
    protected $ioManager;

    /**
     * @var ComposerInstalledVersionParser
     */
    protected $composerParser;

    /**
     * SymfonyEnvironmentParser constructor.
     *
     * @param ComposerInstalledVersionParser $composerParser
     */
    public function __construct(IoManager $ioManager, ComposerInstalledVersionParser $composerParser)
    {
        $this->ioManager = $ioManager;
        $this->composerParser = $composerParser;
    }

    /**
     * Different projects and versions contains different packages, so we need to check more then one option.
     *
     * @param $projectWorkDir
     *
     * @return bool|string
     */
    public function getSymfonyVersion(string $projectWorkDir)
    {
        $symfonyPackages = [
            'symfony/symfony',
            'symfony/config',
            'symfony/framework-bundle',
        ];
        foreach ($symfonyPackages as $symfonyPackage) {
            $packageVersion = $this->composerParser->get($projectWorkDir, $symfonyPackage);
            if ($packageVersion) {
                return $packageVersion;
            }
        }

        return false;
    }

    public function readSymfonyBinDir(string $projectWorkDir, string $default = null)
    {
        $byExtra = $this->composerParser->read($projectWorkDir, 'extra.symfony-bin-dir');
        if ($byExtra) {
            return $byExtra;
        }

        $byConfig = $this->composerParser->read($projectWorkDir, 'config.bin-dir');

        return $byConfig ?: $default;
    }

    public function getSymfonyEnvironmentVariables(string $projectWorkDir): array
    {
        // Megpróbáljuk kiolvasni a használt SF verziót, már ha létezik
        try {
            $symfonyVersion = $this->getSymfonyVersion($projectWorkDir);
        } catch (InvalidComposerVersionNumber $e) {
            $symfonyVersion = false;
        }

        if (!$symfonyVersion) {
            $symfonyVersionQuestion = new ChoiceQuestion(
                'Which symfony version do you want to use? [<info>4.* (LTE)</info>]',
                ['4.* (LTE)', '3.* (eZ project)', '2.* [deprecated]'],
                0
            );
            $symfonyVersion = $this->ioManager->ask($symfonyVersionQuestion);
        }

        $variables = [];
        switch (substr($symfonyVersion, 0, 2)) {
            case '4.':
                $variables[static::VARIABLE_VERSION]        = 4;
                $variables[static::VARIABLE_CONSOLE_CMD]    = 'bin/console';
                $variables[static::VARIABLE_BIN_DIR]        = $this->readSymfonyBinDir($projectWorkDir, 'vendor/bin');
                $variables[static::VARIABLE_SHARED_DIRS]    = 'var';
                $variables[static::VARIABLE_WEB_DIRECTORY]  = 'public';
                $variables[static::VARIABLE_INDEX_FILE]     = 'index.php';
                break;
            case '3.':
                $variables[static::VARIABLE_VERSION]        = 3;
                $variables[static::VARIABLE_CONSOLE_CMD]    = 'bin/console';
                $variables[static::VARIABLE_BIN_DIR]        = $this->readSymfonyBinDir($projectWorkDir, 'vendor/bin');
                $variables[static::VARIABLE_SHARED_DIRS]    = 'var';
                $variables[static::VARIABLE_WEB_DIRECTORY]  = 'web';
                $variables[static::VARIABLE_INDEX_FILE]     = 'app.php';
                break;
            case '2.':
                $variables[static::VARIABLE_VERSION]        = 2;
                $variables[static::VARIABLE_CONSOLE_CMD]    = 'app/console';
                $variables[static::VARIABLE_BIN_DIR]        = $this->readSymfonyBinDir($projectWorkDir, 'bin');
                $variables[static::VARIABLE_SHARED_DIRS]    = 'app/cache app/logs';
                $variables[static::VARIABLE_WEB_DIRECTORY]  = 'web';
                $variables[static::VARIABLE_INDEX_FILE]     = 'app.php';
                break;
            default:
                throw new \InvalidArgumentException('Invalid selection! Missiong settings!');
        }

        return $variables;
    }
}
