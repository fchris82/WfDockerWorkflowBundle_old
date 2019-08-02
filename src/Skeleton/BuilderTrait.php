<?php declare(strict_types=1);
/**
 * Created by IntelliJ IDEA.
 * User: chris
 * Date: 2018.11.27.
 * Time: 11:25
 */

namespace Wf\DockerWorkflowBundle\Skeleton;

use Symfony\Component\EventDispatcher\EventDispatcher;
use Symfony\Component\Filesystem\Filesystem;
use Wf\DockerWorkflowBundle\Event\SkeletonBuild\DumpFileEvent;
use Wf\DockerWorkflowBundle\Event\SkeletonBuildBaseEvents;
use Wf\DockerWorkflowBundle\Exception\SkipSkeletonFileException;
use Wf\DockerWorkflowBundle\Skeleton\FileType\ExecutableSkeletonFile;
use Wf\DockerWorkflowBundle\Skeleton\FileType\SkeletonDirectory;
use Wf\DockerWorkflowBundle\Skeleton\FileType\SkeletonFile;

trait BuilderTrait
{
    /**
     * @var Filesystem
     */
    protected $fileSystem;

    /**
     * @var EventDispatcher
     */
    protected $eventDispatcher;

    abstract protected function eventBeforeDumpFile(DumpFileEvent $event): void;

    abstract protected function eventBeforeDumpTargetExists(DumpFileEvent $event): void;

    abstract protected function eventAfterDumpFile(DumpFileEvent $event): void;

    abstract protected function eventSkipDumpFile(DumpFileEvent $event): void;

    /**
     * @param array|SkeletonFile[] $skeletonFiles
     */
    protected function dumpSkeletonFiles(array $skeletonFiles): void
    {
        foreach ($skeletonFiles as $skeletonFile) {
            $event = new DumpFileEvent($this, $skeletonFile, $this->fileSystem);
            try {
                $this->eventBeforeDumpFile($event);
                $this->eventDispatcher->dispatch($event, SkeletonBuildBaseEvents::BEFORE_DUMP_FILE);

                if ($this->fileSystem->exists($skeletonFile->getFullTargetPathname())) {
                    $this->eventBeforeDumpTargetExists($event);
                    $this->eventDispatcher->dispatch($event, SkeletonBuildBaseEvents::BEFORE_DUMP_TARGET_EXISTS);
                }

                $skeletonFile = $event->getSkeletonFile();
                if ($skeletonFile instanceof SkeletonDirectory) {
                    $this->fileSystem->mkdir($skeletonFile->getFullTargetPathname());
                } elseif (SkeletonFile::HANDLE_EXISTING_APPEND == $skeletonFile->getHandleExisting()) {
                    $this->fileSystem->appendToFile(
                        $skeletonFile->getFullTargetPathname(),
                        $skeletonFile->getContents()
                    );
                } else {
                    $this->fileSystem->dumpFile(
                        $skeletonFile->getFullTargetPathname(),
                        $skeletonFile->getContents()
                    );
                }

                if ($skeletonFile instanceof ExecutableSkeletonFile) {
                    $this->fileSystem->chmod($skeletonFile->getRelativePathname(), $skeletonFile->getPermission());
                }

                $this->eventDispatcher->dispatch($event, SkeletonBuildBaseEvents::AFTER_DUMP_FILE);
                $this->eventAfterDumpFile($event);
            } catch (SkipSkeletonFileException $e) {
                $this->eventSkipDumpFile($event);
                $this->eventDispatcher->dispatch($event, SkeletonBuildBaseEvents::SKIP_DUMP_FILE);
            }
        }
    }
}
