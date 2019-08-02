<?php declare(strict_types=1);
/**
 * Created by IntelliJ IDEA.
 * User: chris
 * Date: 2018.11.26.
 * Time: 17:12
 */

namespace Docker\WorkflowBundle\Event;

use Docker\WorkflowBundle\Event\SkeletonBuild\DumpFileEvent;
use Docker\WorkflowBundle\Event\SkeletonBuild\PostBuildSkeletonFileEvent;
use Docker\WorkflowBundle\Event\SkeletonBuild\PostBuildSkeletonFilesEvent;
use Docker\WorkflowBundle\Event\SkeletonBuild\PreBuildSkeletonFileEvent;
use Docker\WorkflowBundle\Event\SkeletonBuild\PreBuildSkeletonFilesEvent;

class SkeletonBuildBaseEvents
{
    /**
     * @see PreBuildSkeletonFilesEvent
     */
    const BEFORE_BUILD_FILES = 'wf.skeleton.before_build_skeleton_files';
    /**
     * @see PostBuildSkeletonFilesEvent
     */
    const AFTER_BUILD_FILES = 'wf.skeleton.after_build_skeleton_files';

    /**
     * @see PreBuildSkeletonFileEvent
     */
    const BEFORE_BUILD_FILE = 'wf.skeleton.before_build_skeleton_file';
    /**
     * @see PostBuildSkeletonFileEvent
     */
    const AFTER_BUILD_FILE = 'wf.skeleton.after_build_skeleton_file';

    /**
     * @see DumpFileEvent
     */
    const BEFORE_DUMP_FILE = 'wf.skeleton.before_dump_skeleton_file';
    const BEFORE_DUMP_TARGET_EXISTS = 'wf.skeleton.before_dump_target_exists';
    const AFTER_DUMP_FILE = 'wf.skeleton.after_dump_skeleton_file';
    const SKIP_DUMP_FILE = 'wf.skeleton.skip_dump_skeleton_file';
}
