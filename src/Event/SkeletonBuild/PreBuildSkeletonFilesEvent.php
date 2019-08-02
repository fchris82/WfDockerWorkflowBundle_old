<?php declare(strict_types=1);
/**
 * Created by IntelliJ IDEA.
 * User: chris
 * Date: 2018.11.28.
 * Time: 13:02
 */

namespace Wf\DockerWorkflowBundle\Event\SkeletonBuild;

use Wf\DockerWorkflowBundle\Event\NamespacedEvent;
use Wf\DockerWorkflowBundle\Skeleton\SkeletonTwigFileInfo;

class PreBuildSkeletonFilesEvent extends NamespacedEvent
{
    /**
     * @var array
     */
    protected $skeletonVars;

    /**
     * @var array
     */
    protected $buildConfig;

    /**
     * @var array|SkeletonTwigFileInfo[]
     */
    protected $skeletonFileInfos = [];

    /**
     * PreBuildSkeletonFilesEvent constructor.
     *
     * @param string|object $namespace
     * @param array         $skeletonVars
     * @param array         $buildConfig
     *
     * @codeCoverageIgnore Simple setter
     */
    public function __construct($namespace, array $skeletonVars, array $buildConfig)
    {
        parent::__construct($namespace);
        $this->skeletonVars = $skeletonVars;
        $this->buildConfig = $buildConfig;
    }

    /**
     * @return array
     *
     * @codeCoverageIgnore Simple getter
     */
    public function getSkeletonVars(): array
    {
        return $this->skeletonVars;
    }

    /**
     * @param array $skeletonVars
     *
     * @return $this
     *
     * @codeCoverageIgnore Simple setter
     */
    public function setSkeletonVars(array $skeletonVars): self
    {
        $this->skeletonVars = $skeletonVars;

        return $this;
    }

    /**
     * @return array
     *
     * @codeCoverageIgnore Simple getter
     */
    public function getBuildConfig(): array
    {
        return $this->buildConfig;
    }

    /**
     * @param array $buildConfig
     *
     * @return $this
     *
     * @codeCoverageIgnore Simple setter
     */
    public function setBuildConfig(array $buildConfig): self
    {
        $this->buildConfig = $buildConfig;

        return $this;
    }

    /**
     * @param SkeletonTwigFileInfo $fileInfo
     *
     * @return PreBuildSkeletonFilesEvent
     *
     * @codeCoverageIgnore Simple setter
     */
    public function addSkeletonFileInfo(SkeletonTwigFileInfo $fileInfo): self
    {
        $this->skeletonFileInfos[] = $fileInfo;

        return $this;
    }

    /**
     * @return array|SkeletonTwigFileInfo[]
     *
     * @codeCoverageIgnore Simple getter
     */
    public function getSkeletonFileInfos(): array
    {
        return $this->skeletonFileInfos;
    }

    /**
     * @param array|SkeletonTwigFileInfo[] $skeletonFileInfos
     *
     * @return $this
     *
     * @codeCoverageIgnore Simple setter
     */
    public function setSkeletonFileInfos($skeletonFileInfos): self
    {
        $this->skeletonFileInfos = $skeletonFileInfos;

        return $this;
    }
}
