<?php declare(strict_types=1);
/**
 * Created by IntelliJ IDEA.
 * User: chris
 * Date: 2018.11.30.
 * Time: 15:21
 */

namespace Wf\DockerWorkflowBundle\Environment;

use Symfony\Component\Filesystem\Exception\FileNotFoundException;
use Wf\DockerWorkflowBundle\Exception\InvalidComposerVersionNumber;

class EzEnvironmentParser extends SymfonyEnvironmentParser
{
    public function isEzProject($workingDirectory): bool
    {
        $ezVersion = false;
        $kaliopVersion = false;
        $ezYmlExists = $this->composerParser->getFilesystem()->exists($workingDirectory . '/.ez.yml');
        try {
            $ezVersion = $this->composerParser->get(
                $workingDirectory,
                'ezsystems/ezpublish-kernel'
            );
            $kaliopVersion = $this->composerParser->get(
                $workingDirectory,
                'kaliop/ezmigrationbundle'
            );
        } catch (InvalidComposerVersionNumber $e) {
            return true;
        } catch (FileNotFoundException $e) {
            return false;
        }

        return $ezVersion || $kaliopVersion || $ezYmlExists;
    }

    /**
     * @param $projectWorkDir
     *
     * @return array
     *
     * @codeCoverageIgnore Simple extender
     */
    public function getSymfonyEnvironmentVariables(string $projectWorkDir): array
    {
        $variables = parent::getSymfonyEnvironmentVariables($projectWorkDir);
        $variables['is_ez'] = $this->isEzProject($projectWorkDir);

        return $variables;
    }
}
