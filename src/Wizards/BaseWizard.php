<?php declare(strict_types=1);
/**
 * Created by IntelliJ IDEA.
 * User: chris
 * Date: 2017.08.11.
 * Time: 15:54.
 */

namespace Wf\DockerWorkflowBundle\Wizards;

use Symfony\Component\Console\Question\Question;
use Symfony\Component\EventDispatcher\EventDispatcher;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;
use Wf\DockerWorkflowBundle\Environment\Commander;
use Wf\DockerWorkflowBundle\Environment\IoManager;
use Wf\DockerWorkflowBundle\Event\Wizard\BuildWizardEvent;
use Wf\DockerWorkflowBundle\Exception\WizardHasAlreadyBuiltException;
use Wf\DockerWorkflowBundle\Exception\WizardSomethingIsRequiredException;
use Wf\DockerWorkflowBundle\Wizard\WizardInterface;

/**
 * Class BaseSkeleton.
 */
abstract class BaseWizard implements WizardInterface
{
    /**
     * @var IoManager
     */
    protected $ioManager;

    /**
     * @var Commander
     */
    protected $commander;

    /**
     * @var EventDispatcher
     */
    protected $eventDispatcher;

    public function __construct(IoManager $ioManager, Commander $commander, EventDispatcherInterface $eventDispatcher)
    {
        $this->ioManager = $ioManager;
        $this->commander = $commander;
        $this->eventDispatcher = $eventDispatcher;
    }

    abstract public function getDefaultName(): string;

    public function getDefaultGroup(): string
    {
        return '';
    }

    public function getInfo(): string
    {
        return '';
    }

    public function isHidden(): bool
    {
        return false;
    }

    /**
     * @param Question $question
     *
     * @return mixed
     */
    public function ask(Question $question)
    {
        return $this->ioManager->ask($question);
    }

    /**
     * runBuild()
     *      ├── initBuild()
     *      │   ├── checkRequires()
     *      │   └── init()
     *      │
     *      ├── build()
     *      │
     *      └── cleanUp()
     *
     * @param $targetProjectDirectory
     *
     * @throws WizardHasAlreadyBuiltException
     *
     * @return string
     */
    public function runBuild(string $targetProjectDirectory): string
    {
        $event = new BuildWizardEvent($targetProjectDirectory);
        $this->initBuild($event);
        $this->build($event);
        $this->cleanUp($event);

        return $targetProjectDirectory;
    }

    /**
     * @param BuildWizardEvent $event
     *
     * @throws WizardHasAlreadyBuiltException
     */
    protected function initBuild(BuildWizardEvent $event): void
    {
        $this->checkRequires($event->getWorkingDirectory());
        if ($this->isBuilt($event->getWorkingDirectory())) {
            throw new WizardHasAlreadyBuiltException($this, $event->getWorkingDirectory());
        }
        $this->init($event);
    }

    protected function init(BuildWizardEvent $event): void
    {
        // User function
    }

    abstract protected function build(BuildWizardEvent $event): void;

    protected function cleanUp(BuildWizardEvent $event): void
    {
        // User function
    }

    protected function call(string $workingDirectory, self $wizard): void
    {
        try {
            $wizard->checkRequires($workingDirectory);
            if (!$wizard->isBuilt($workingDirectory)) {
                try {
                    $wizard->runBuild($workingDirectory);
                } catch (WizardHasAlreadyBuiltException $e) {
                    $this->ioManager->getIo()->note($e->getMessage());
                }
            }
        } catch (WizardSomethingIsRequiredException $e) {
            $this->ioManager->writeln($e->getMessage());
        }
    }

    public function runCmdInContainer(string $cmd, string $workdir = null): string
    {
        return $this->commander->runCmdInContainer(
            $cmd,
            $this->getDockerImage(),
            $this->getDockerCmdExtraParameters($workdir),
            $workdir
        );
    }

    protected function getDockerCmdExtraParameters(string $targetProjectDirectory): string
    {
        return '';
    }

    /**
     * We are using this when call a self::runCmdInContainer() function.
     *
     * @return string
     *
     * @see BaseWizard::runCmdInContainer()
     */
    protected function getDockerImage(): string
    {
        return 'fchris82/wf';
    }

    protected function getDockerShell(): string
    {
        return '/bin/bash';
    }

    public function isBuilt(string $targetProjectDirectory): bool
    {
        return false;
    }

    /**
     * @param string $targetProjectDirectory
     *
     * @return bool
     *
     * @throw WizardSomethingIsRequiredException
     */
    public function checkRequires(string $targetProjectDirectory): bool
    {
        return true;
    }
}
