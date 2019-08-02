<?php declare(strict_types=1);
/**
 * Created by IntelliJ IDEA.
 * User: chris
 * Date: 2018.03.28.
 * Time: 13:28
 */

namespace Wf\DockerWorkflowBundle\Event\Configuration;

use Symfony\Contracts\EventDispatcher\Event;

class BuildInitEvent extends Event
{
    /**
     * @var array
     */
    protected $config;

    /**
     * @var string
     */
    protected $projectPath;

    /**
     * @var string
     */
    protected $targetDirectory;

    /**
     * @var string
     */
    protected $configHash;

    /**
     * @var array
     */
    protected $parameters = [];

    /**
     * BuildInitEvent constructor.
     *
     * @param array  $config
     * @param string $projectPath
     * @param string $targetDirectory
     * @param string $configHash
     */
    public function __construct(array $config, string $projectPath, string $targetDirectory, string $configHash)
    {
        $this->config = $config;
        $this->projectPath = $projectPath;
        $this->targetDirectory = $targetDirectory;
        $this->configHash = $configHash;
    }

    /**
     * @return array
     *
     * @codeCoverageIgnore Simple getter
     */
    public function getConfig(): array
    {
        return $this->config;
    }

    /**
     * @param array $config
     *
     * @return $this
     *
     * @codeCoverageIgnore Simple setter
     */
    public function setConfig(array $config): self
    {
        $this->config = $config;

        return $this;
    }

    /**
     * @return string
     *
     * @codeCoverageIgnore Simple getter
     */
    public function getProjectPath(): string
    {
        return $this->projectPath;
    }

    /**
     * @return string
     *
     * @codeCoverageIgnore Simple getter
     */
    public function getTargetDirectory(): string
    {
        return $this->targetDirectory;
    }

    /**
     * @return string
     *
     * @codeCoverageIgnore Simple getter
     */
    public function getConfigHash(): string
    {
        return $this->configHash;
    }

    /**
     * @return array
     */
    public function getParameters(): array
    {
        $baseParameters = [
            '%wf.project_path%'     => $this->projectPath,
            '%wf.target_directory%' => $this->targetDirectory,
            '%wf.config_hash%'      => $this->configHash,
        ];
        // Add ENV-s
        $envParameters = [];
        foreach ($_ENV as $name => $value) {
            $key = '%env.' . $name . '%';
            $envParameters[$key] = $value;
        }

        return array_merge(
            $baseParameters,
            $envParameters,
            $this->parameters
        );
    }

    /**
     * @param $name
     * @param $value
     *
     * @return $this
     *
     * @codeCoverageIgnore Simple setter
     */
    public function setParameter(string $name, $value): self
    {
        $this->parameters[$name] = $value;

        return $this;
    }
}
