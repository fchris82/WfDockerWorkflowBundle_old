<?php declare(strict_types=1);
/**
 * Created by IntelliJ IDEA.
 * User: chris
 * Date: 2018.11.26.
 * Time: 17:33
 */

namespace Webtown\WorkflowBundle\Event\SkeletonBuild;

use Symfony\Component\Finder\SplFileInfo;
use Webtown\WorkflowBundle\Skeleton\FileType\SkeletonFile;

class PostBuildSkeletonFileEvent extends PreBuildSkeletonFileEvent
{
    /**
     * PostBuildSkeletonFileEvent constructor.
     *
     * @param string|object $namespace
     * @param SkeletonFile  $skeletonFile
     * @param SplFileInfo   $sourceFileInfo
     * @param array         $skeletonVars
     * @param array         $buildConfig
     *
     * @codeCoverageIgnore Simple setter
     */
    public function __construct($namespace, SkeletonFile $skeletonFile, SplFileInfo $sourceFileInfo, array $skeletonVars, array $buildConfig)
    {
        $this->skeletonFile = $skeletonFile;

        parent::__construct($namespace, $sourceFileInfo, $skeletonVars, $buildConfig);
    }
}
