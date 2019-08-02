<?php declare(strict_types=1);
/**
 * Created by IntelliJ IDEA.
 * User: chris
 * Date: 2019.01.23.
 * Time: 11:23
 */

namespace Wf\DockerWorkflowBundle\Tests\Dummy\Recipes\ConflictWithSimpleEventListener;

use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;
use Twig\Environment;
use Wf\DockerWorkflowBundle\Event\RegisterEventListenersInterface;
use Wf\DockerWorkflowBundle\Event\SkeletonBuild\PreBuildSkeletonFilesEvent;
use Wf\DockerWorkflowBundle\Event\SkeletonBuildBaseEvents;
use Wf\DockerWorkflowBundle\Recipes\BaseRecipe;
use Wf\DockerWorkflowBundle\Test\Dummy\Filesystem;

/**
 * With this recipe we can test the SkeletonBuildBaseEvents::BEFORE_DUMP_TARGET_EXISTS event. This recipe create a
 * README.md file to the `simple_event_listener/templates/README.md` what the SimpleEventListener wants to create also.
 */
class ConflictWithSimpleEventListenerRecipe extends BaseRecipe implements RegisterEventListenersInterface
{
    /**
     * @var Filesystem
     */
    protected $filesystem;

    /**
     * ConflictWithSimpleEventListenerRecipe constructor.
     *
     * @param Filesystem $filesystem
     */
    public function __construct(Filesystem $filesystem, Environment $twig, EventDispatcherInterface $eventDispatcher)
    {
        $this->filesystem = $filesystem;
        parent::__construct($twig, $eventDispatcher);
    }

    public function getName(): string
    {
        return 'conflict_with_simple_event_listener';
    }

    public function registerEventListeners(EventDispatcherInterface $eventDispatcher): void
    {
        $eventDispatcher->addListener(SkeletonBuildBaseEvents::BEFORE_BUILD_FILES, [$this, 'createFiles']);
    }

    public function createFiles(PreBuildSkeletonFilesEvent $event)
    {
        // Only run once
        if ($event->isNamespace($this)) {
            $this->filesystem->dumpFile('alias/.wf/simple_event_listener/templates/README.md', "Existing\n");
            $this->filesystem->dumpFile('alias/.wf/simple_event_listener/templates/test.sh', "# Existing\n");
        }
    }
}
