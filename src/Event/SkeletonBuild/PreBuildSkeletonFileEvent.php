<?php declare(strict_types=1);
/**
 * Created by IntelliJ IDEA.
 * User: chris
 * Date: 2018.11.26.
 * Time: 17:22
 */

namespace Wf\DockerWorkflowBundle\Event\SkeletonBuild;

use Symfony\Component\Finder\SplFileInfo;
use Wf\DockerWorkflowBundle\Event\NamespacedEvent;
use Wf\DockerWorkflowBundle\Skeleton\FileType\SkeletonFile;

class PreBuildSkeletonFileEvent extends NamespacedEvent
{
    /**
     * @var SplFileInfo
     */
    protected $sourceFileInfo;

    /**
     * @var array
     */
    protected $skeletonVars;

    /**
     * @var array
     */
    protected $buildConfig;

    /**
     * @var SkeletonFile
     */
    protected $skeletonFile;

    /**
     * PreBuildSkeletonFileEvent constructor.
     *
     * @param string|object $namespace
     * @param SplFileInfo   $sourceFileInfo
     * @param array         $skeletonVars
     * @param array         $buildConfig
     *
     * @codeCoverageIgnore Simple setter
     */
    public function __construct($namespace, SplFileInfo $sourceFileInfo, array $skeletonVars, array $buildConfig)
    {
        parent::__construct($namespace);
        $this->sourceFileInfo = $sourceFileInfo;
        $this->skeletonVars = $skeletonVars;
        $this->buildConfig = $buildConfig;
    }

    /**
     * @return SplFileInfo
     *
     * @codeCoverageIgnore Simple getter
     */
    public function getSourceFileInfo(): SplFileInfo
    {
        return $this->sourceFileInfo;
    }

    /**
     * @param SplFileInfo $sourceFileInfo
     *
     * @return $this
     *
     * @codeCoverageIgnore Simple setter
     */
    public function setSourceFileInfo(SplFileInfo $sourceFileInfo): self
    {
        $this->sourceFileInfo = $sourceFileInfo;

        return $this;
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
    public function setSkeletonVars(array $skeletonVars)
    {
        $this->skeletonVars = $skeletonVars;

        return $this;
    }

    /**
     * @param string $key
     * @param null   $default
     *
     * @return mixed|null
     *
     * @codeCoverageIgnore Simple getter
     */
    public function getSkeletonVar(string $key, $default = null)
    {
        if (!\array_key_exists($key, $this->skeletonVars)) {
            return $default;
        }

        return $this->skeletonVars[$key];
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
    public function setBuildConfig(array $buildConfig)
    {
        $this->buildConfig = $buildConfig;

        return $this;
    }

    /**
     * @return SkeletonFile|null
     *
     * @codeCoverageIgnore Simple getter
     */
    public function getSkeletonFile(): ?SkeletonFile
    {
        return $this->skeletonFile;
    }

    /**
     * @param SkeletonFile $skeletonFile
     *
     * @return $this
     *
     * @codeCoverageIgnore Simple setter
     */
    public function setSkeletonFile(SkeletonFile $skeletonFile)
    {
        $this->skeletonFile = $skeletonFile;

        return $this;
    }
}
