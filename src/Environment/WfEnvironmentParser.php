<?php declare(strict_types=1);
/**
 * Created by IntelliJ IDEA.
 * User: chris
 * Date: 2018.11.30.
 * Time: 13:10
 */

namespace Wf\DockerWorkflowBundle\Environment;

use Symfony\Component\Filesystem\Filesystem;
use Wf\DockerWorkflowBundle\Configuration\Configuration;

class WfEnvironmentParser
{
    /**
     * @var Configuration
     */
    protected $configuration;

    /**
     * @var Filesystem
     */
    protected $fileSystem;

    /**
     * @var array
     */
    protected $workflowConfigurationCache = [];

    /**
     * WfEnvironmentParser constructor.
     *
     * @param Configuration $configuration
     * @param Filesystem    $fileSystem
     */
    public function __construct(Configuration $configuration, Filesystem $fileSystem)
    {
        $this->configuration = $configuration;
        $this->fileSystem = $fileSystem;
    }

    public function wfIsInitialized(string $projectDirectory): bool
    {
        return $this->fileSystem->exists($projectDirectory . '/.wf.yml.dist')
            || $this->fileSystem->exists($projectDirectory . '/.wf.yml');
    }

    public function getWorkflowConfiguration(string $workingDirectory): array
    {
        if (!$this->wfIsInitialized($workingDirectory)) {
            throw new \InvalidArgumentException('Missing configuration files!');
        }

        if (!\array_key_exists($workingDirectory, $this->workflowConfigurationCache)) {
            $wfFiles = [
                '.wf.yml',
                '.wf.yml.dist',
            ];
            foreach ($wfFiles as $wfFile) {
                $configFilePath = $workingDirectory . '/' . $wfFile;
                if ($this->fileSystem->exists($configFilePath)) {
                    $this->workflowConfigurationCache[$workingDirectory] = $this->configuration->loadConfig($configFilePath);
                    break;
                }
            }
        }

        return $this->workflowConfigurationCache[$workingDirectory];
    }
}
