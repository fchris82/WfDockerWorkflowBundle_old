<?php declare(strict_types=1);
/**
 * Created by IntelliJ IDEA.
 * User: chris
 * Date: 2017.08.11.
 * Time: 15:54.
 */

namespace Wf\DockerWorkflowBundle\Wizards;

use Symfony\Component\Console\Helper\Table;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;
use Twig\Environment;
use Wf\DockerWorkflowBundle\Environment\Commander;
use Wf\DockerWorkflowBundle\Environment\IoManager;
use Wf\DockerWorkflowBundle\Event\SkeletonBuild\DumpFileEvent;
use Wf\DockerWorkflowBundle\Event\SkeletonBuild\PostBuildSkeletonFileEvent;
use Wf\DockerWorkflowBundle\Event\SkeletonBuild\PostBuildSkeletonFilesEvent;
use Wf\DockerWorkflowBundle\Event\SkeletonBuild\PreBuildSkeletonFileEvent;
use Wf\DockerWorkflowBundle\Event\SkeletonBuild\PreBuildSkeletonFilesEvent;
use Wf\DockerWorkflowBundle\Event\Wizard\BuildWizardEvent;
use Wf\DockerWorkflowBundle\Exception\WizardHasAlreadyBuiltException;
use Wf\DockerWorkflowBundle\Skeleton\BuilderTrait;
use Wf\DockerWorkflowBundle\Skeleton\FileType\SkeletonFile;
use Wf\DockerWorkflowBundle\Skeleton\SkeletonManagerTrait;

/**
 * Class BaseSkeleton.
 *
 * Wizard, that has skeleton files that it decorates an existing project with or creates a new.
 */
abstract class BaseSkeletonWizard extends BaseWizard
{
    use SkeletonManagerTrait;
    use BuilderTrait;

    /**
     * @var array
     */
    protected $workflowConfigurationCache;

    /**
     * BaseSkeleton constructor.
     *
     * @param IoManager                $ioManager
     * @param EventDispatcherInterface $eventDispatcher
     * @param Environment              $twig
     * @param Filesystem               $filesystem
     */
    public function __construct(
        IoManager $ioManager,
        Commander $commander,
        EventDispatcherInterface $eventDispatcher,
        Environment $twig,
        Filesystem $filesystem
    ) {
        $this->twig = $twig;
        $this->fileSystem = $filesystem;
        parent::__construct($ioManager, $commander, $eventDispatcher);
    }

    /**
     * Here you can ask data and variables from user or set them.
     */
    protected function readSkeletonVars(BuildWizardEvent $event): array
    {
        return [];
    }

    /**
     * @param BuildWizardEvent $event
     *
     * @throws WizardHasAlreadyBuiltException
     * @throws \Exception
     */
    protected function initBuild(BuildWizardEvent $event): void
    {
        parent::initBuild($event);

        $event->setSkeletonVars($this->readSkeletonVars($event));

        $this->printHeader($event);
        $this->doBuildFiles($event);
    }

    /**
     * @param BuildWizardEvent $event
     *
     * @throws \Exception
     * @throws \Twig_Error_Loader
     * @throws \Twig_Error_Runtime
     * @throws \Twig_Error_Syntax
     */
    protected function doBuildFiles(BuildWizardEvent $event): void
    {
        $skeletonFiles = $this->buildSkeletonFiles($event->getSkeletonVars());
        $this->dumpSkeletonFiles($skeletonFiles);
    }

    protected function printHeader(BuildWizardEvent $event): void
    {
        $output = $this->ioManager->getOutput();
        $output->writeln("\n <comment>⏲</comment> <info>Start build...</info>\n");

        $table = new Table($output);
        $table
            ->setHeaders(['Placeholder', 'Value']);
        foreach ($event->getSkeletonVars() as $key => $value) {
            $table->addRow([
                $key,
                \is_array($value) || \is_object($value)
                    ? json_encode($value, JSON_PRETTY_PRINT)
                    : $value,
            ]);
        }
        $table->render();
    }

    public function isBuilt(string $targetProjectDirectory): bool
    {
        if ($this->getBuiltCheckFile()) {
            return $this->fileSystem->exists($targetProjectDirectory . '/' . $this->getBuiltCheckFile());
        }

        return false;
    }

    protected function getBuiltCheckFile(): ?string
    {
        return null;
    }

    protected function eventBeforeBuildFiles(PreBuildSkeletonFilesEvent $event): void
    {
    }

    protected function eventBeforeBuildFile(PreBuildSkeletonFileEvent $preBuildSkeletonFileEvent): void
    {
    }

    protected function eventAfterBuildFile(PostBuildSkeletonFileEvent $postBuildSkeletonFileEvent): void
    {
    }

    protected function eventAfterBuildFiles(PostBuildSkeletonFilesEvent $event): void
    {
    }

    protected function eventBeforeDumpFile(DumpFileEvent $event): void
    {
        if ($this->isWfConfigYamlFile($event->getSkeletonFile())) {
            $content = $event->getSkeletonFile()->getContents();
            $helpComment = <<<EOS
# Available configuration parameters
# ==================================
#
# List all:
#   wf --config-dump
#
# List only names:
#   wf --config-dump --only-recipes
#
# List only a recipe:
#   wf --config-dump --recipe=symfony3
#
# Save to a file to edit:
#    wf --config-dump --no-ansi > .wf.yml
#
# Add new recipe:
#    wf --config-dump --recipe=php --no-ansi >> .wf.yml
#
# ----------------------------------------------------------------------------------------------------------------------

EOS;
            $event->getSkeletonFile()->setContents($helpComment . $content);
        }
    }

    protected function eventBeforeDumpTargetExists(DumpFileEvent $event): void
    {
    }

    protected function eventAfterDumpFile(DumpFileEvent $event): void
    {
        $this->printDumpedFile($event);
    }

    protected function eventSkipDumpFile(DumpFileEvent $event): void
    {
    }

    protected function printDumpedFile(DumpFileEvent $event): void
    {
        $skeletonFile = $event->getSkeletonFile();
        $status = SkeletonFile::HANDLE_EXISTING_APPEND == $skeletonFile->getHandleExisting()
            ? 'modified'
            : 'created'
        ;

        $this->ioManager->getOutput()->writeln(sprintf(
            '<info> ✓ The </info>%s/<comment>%s</comment><info> file has been %s.</info>',
            $skeletonFile->getRelativePath(),
            $skeletonFile->getFileName(),
            $status
        ));
    }

    protected function isWfConfigYamlFile(SkeletonFile $skeletonFile): bool
    {
        $filename = $skeletonFile->getFileName();
        $extension = $skeletonFile->getBaseFileInfo()->getExtension();

        if (0 !== strpos($filename, '.wf')) {
            return false;
        }

        if (\in_array($extension, ['yml', 'yaml'])
            || '.yml.dist' == substr($filename, -9)
            || '.yaml.dist' == substr($filename, -10)
        ) {
            return true;
        }

        return false;
    }
}
