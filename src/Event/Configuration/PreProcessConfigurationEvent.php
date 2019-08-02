<?php declare(strict_types=1);
/**
 * Created by IntelliJ IDEA.
 * User: chris
 * Date: 2018.03.28.
 * Time: 13:28
 */

namespace Wf\DockerWorkflowBundle\Event\Configuration;

use Symfony\Contracts\EventDispatcher\Event;

class PreProcessConfigurationEvent extends Event
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
     * @var string|null
     */
    protected $wfVersion;

    /**
     * PreProcessConfigurationEvent constructor.
     *
     * @param array       $config
     * @param string      $projectPath
     * @param string|null $wfVersion
     */
    public function __construct(array $config, string $projectPath, ?string $wfVersion)
    {
        $this->config = $config;
        $this->projectPath = $projectPath;
        $this->wfVersion = $wfVersion;
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
     * @param string $projectPath
     *
     * @return $this
     *
     * @codeCoverageIgnore Simple setter
     */
    public function setProjectPath(string $projectPath): self
    {
        $this->projectPath = $projectPath;

        return $this;
    }

    /**
     * @return string|null
     *
     * @codeCoverageIgnore Simple getter
     */
    public function getWfVersion(): ?string
    {
        return $this->wfVersion;
    }
}
