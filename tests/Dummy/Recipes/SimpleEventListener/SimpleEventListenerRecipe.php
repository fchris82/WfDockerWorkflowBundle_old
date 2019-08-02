<?php declare(strict_types=1);
/**
 * Created by IntelliJ IDEA.
 * User: chris
 * Date: 2019.01.23.
 * Time: 11:23
 */

namespace Webtown\WorkflowBundle\Tests\Dummy\Recipes\SimpleEventListener;

use Webtown\WorkflowBundle\Event\RegisterEventListenersInterface;
use Webtown\WorkflowBundle\Event\SkeletonBuild\DumpFileEvent;
use Webtown\WorkflowBundle\Event\SkeletonBuild\PostBuildSkeletonFilesEvent;
use Webtown\WorkflowBundle\Event\SkeletonBuildBaseEvents;
use Webtown\WorkflowBundle\Exception\SkipSkeletonFileException;
use Webtown\WorkflowBundle\Recipes\BaseRecipe;
use Webtown\WorkflowBundle\Recipes\HiddenRecipe;
use Webtown\WorkflowBundle\Skeleton\FileType\ExecutableSkeletonFile;
use Webtown\WorkflowBundle\Skeleton\FileType\SkeletonDirectory;
use Webtown\WorkflowBundle\Skeleton\FileType\SkeletonFile;
use Symfony\Component\Finder\SplFileInfo;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;

/**
 * The recipe as an event listener too.
 *
 *  - This recipe collect the skeleton files
 *  - This recipe add a new directory and an extra README.md file to simple recipes (which isn't hidden or system recipes)
 */
class SimpleEventListenerRecipe extends BaseRecipe implements RegisterEventListenersInterface
{
    /**
     * @var array|string[]
     */
    protected $files = [];

    public function getName(): string
    {
        return 'simple_event_listener';
    }

    public function registerEventListeners(EventDispatcherInterface $eventDispatcher): void
    {
        $eventDispatcher->addListener(SkeletonBuildBaseEvents::BEFORE_DUMP_FILE, [$this, 'skipFile']);
        $eventDispatcher->addListener(SkeletonBuildBaseEvents::BEFORE_DUMP_TARGET_EXISTS, [$this, 'handleExisting']);
        $eventDispatcher->addListener(SkeletonBuildBaseEvents::AFTER_DUMP_FILE, [$this, 'collectFiles']);
        $eventDispatcher->addListener(SkeletonBuildBaseEvents::AFTER_BUILD_FILES, [$this, 'addExtraSkeletonFiles']);
    }

    public function collectFiles(DumpFileEvent $event): void
    {
        $skeletonFile = $event->getSkeletonFile();
        $this->files[] = $skeletonFile->getRelativePathname();
    }

    public function getFiles(): array
    {
        return $this->files;
    }

    public function addExtraSkeletonFiles(PostBuildSkeletonFilesEvent $event): void
    {
        $reflectionClass = new \ReflectionClass($event->getNamespace());
        if ($reflectionClass->isSubclassOf(BaseRecipe::class) && !$reflectionClass->isSubclassOf(HiddenRecipe::class)) {
            // Add an extra empty "example" directory
            $skeletonDir = new SkeletonDirectory(new SplFileInfo(
                __DIR__ . '/examples',
                '',
                'examples'
            ));
            $event->addSkeletonFile($skeletonDir);

            // Add an extra "templates/README.md" file
            $skeletonFile = new SkeletonFile(new SplFileInfo(
                __DIR__ . '/templates/README.md',
                'templates',
                'templates/README.md'
            ));
            $event->addSkeletonFile($skeletonFile);

            // Add an extra executable "test.sh" file
            $skeletonFile = new ExecutableSkeletonFile(new SplFileInfo(
                __DIR__ . '/templates/test.sh',
                'templates',
                'templates/test.sh'
            ));
            $event->addSkeletonFile($skeletonFile);
        }
    }

    public function skipFile(DumpFileEvent $event)
    {
        if ('skip.txt' == $event->getSkeletonFile()->getFileName()) {
            throw new SkipSkeletonFileException();
        }
    }

    /**
     * Add a `.new` suffix to the existing file.
     *
     * @param DumpFileEvent $event
     */
    public function handleExisting(DumpFileEvent $event)
    {
        $skeletonFile = $event->getSkeletonFile();
        switch ($skeletonFile->getFileName()) {
            case 'README.md':
                $skeletonFile->setHandleExisting(SkeletonFile::HANDLE_EXISTING_APPEND);
                break;
            default:
                $currentFilename = $skeletonFile->getFileName();
                $skeletonFile->rename($currentFilename . '.new');
        }
    }
}