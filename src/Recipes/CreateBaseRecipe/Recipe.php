<?php declare(strict_types=1);
/**
 * Created by IntelliJ IDEA.
 * User: chris
 * Date: 2018.11.27.
 * Time: 13:56
 */

namespace Wf\DockerWorkflowBundle\Recipes\CreateBaseRecipe;

use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;
use Twig\Environment as TwigEnvironment;
use Wf\DockerWorkflowBundle\Configuration\Builder;
use Wf\DockerWorkflowBundle\Configuration\Environment;
use Wf\DockerWorkflowBundle\Event\Configuration\BuildInitEvent;
use Wf\DockerWorkflowBundle\Event\ConfigurationEvents;
use Wf\DockerWorkflowBundle\Event\RegisterEventListenersInterface;
use Wf\DockerWorkflowBundle\Event\SkeletonBuild\DumpFileEvent;
use Wf\DockerWorkflowBundle\Event\SkeletonBuild\PostBuildSkeletonFileEvent;
use Wf\DockerWorkflowBundle\Event\SkeletonBuildBaseEvents;
use Wf\DockerWorkflowBundle\Recipes\SystemRecipe;
use Wf\DockerWorkflowBundle\Skeleton\FileType\DockerComposeSkeletonFile;
use Wf\DockerWorkflowBundle\Skeleton\FileType\MakefileSkeletonFile;

class Recipe extends SystemRecipe implements RegisterEventListenersInterface
{
    const NAME = '_';

    /**
     * @var Environment
     */
    protected $environment;

    /**
     * @var string
     */
    protected $makefileName;

    /**
     * @var array|string[]
     */
    protected $makefiles = [];

    /**
     * @var array|string[]
     */
    protected $dockerComposeFiles = [];

    /**
     * Recipe constructor.
     *
     * @param TwigEnvironment          $twig
     * @param EventDispatcherInterface $eventDispatcher
     * @param Environment              $environment
     */
    public function __construct(TwigEnvironment $twig, EventDispatcherInterface $eventDispatcher, Environment $environment)
    {
        parent::__construct($twig, $eventDispatcher);
        $this->environment = $environment;
    }

    public function getName(): string
    {
        return static::NAME;
    }

    /**
     * @param string $targetPath
     * @param array  $recipeConfig Here it is the `$globalConfig`
     * @param array  $globalConfig
     *
     * @return array
     *
     * @see Builder::build()
     */
    public function getSkeletonVars(string $targetPath, array $recipeConfig, array $globalConfig): array
    {
        $dockerComposeFiles = array_map(function ($v) {
            // If the path start with `/` or `~` we won't change, else we put the project path before it
            return \in_array($v[0], ['/', '~']) ? $v : '$(PROJECT_WORKING_DIRECTORY)/' . $v;
        }, $this->dockerComposeFiles);

        return array_merge(parent::getSkeletonVars($targetPath, $recipeConfig, $globalConfig), [
            'wf_target_directory'   => $this->environment->getConfigValue(Environment::CONFIG_WORKING_DIRECTORY),
            'wf_config_file'        => $this->environment->getConfigValue(Environment::CONFIG_CONFIGURATION_FILE),
            'wf_env_file'           => $this->environment->getConfigValue(Environment::CONFIG_ENV_FILE),
            'include_makefiles'     => $this->makefileMultilineFormatter('include %s', $this->makefiles),
            'docker_compose_files'  => $this->makefileMultilineFormatter('DOCKER_CONFIG_FILES := %s', $dockerComposeFiles),
        ]);
    }

    public function init(BuildInitEvent $event): void
    {
        $this->makefileName = $event->getConfigHash() . '.mk';
    }

    public function collectFiles(DumpFileEvent $event): void
    {
        $skeletonFile = $event->getSkeletonFile();

        switch (true) {
            case $skeletonFile instanceof MakefileSkeletonFile:
                $this->makefiles[$skeletonFile->getRelativePathname()] = $skeletonFile->getRelativePathname();
                break;
            case $skeletonFile instanceof DockerComposeSkeletonFile:
                $this->dockerComposeFiles[$skeletonFile->getRelativePathname()] = $skeletonFile->getRelativePathname();
                break;
        }
    }

    protected function renameMakefile(PostBuildSkeletonFileEvent $event): void
    {
        $skeletonFile = $event->getSkeletonFile();
        if ('makefile' == $skeletonFile->getFileName()) {
            $skeletonFile->setFileName($this->makefileName);
        }
    }

    public function getDirectoryName(): string
    {
        return '';
    }

    public function registerEventListeners(EventDispatcherInterface $eventDispatcher): void
    {
        $eventDispatcher->addListener(ConfigurationEvents::BUILD_INIT, [$this, 'init']);
        $eventDispatcher->addListener(SkeletonBuildBaseEvents::AFTER_DUMP_FILE, [$this, 'collectFiles']);
    }

    protected function eventAfterBuildFile(PostBuildSkeletonFileEvent $postBuildSkeletonFileEvent): void
    {
        $this->renameMakefile($postBuildSkeletonFileEvent);
        parent::eventAfterBuildFile($postBuildSkeletonFileEvent);
    }
}
