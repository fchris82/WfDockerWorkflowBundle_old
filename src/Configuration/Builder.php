<?php declare(strict_types=1);
/**
 * Created by IntelliJ IDEA.
 * User: chris
 * Date: 2018.03.10.
 * Time: 17:20
 */

namespace Wf\DockerWorkflowBundle\Configuration;

use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Finder\Finder;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;
use Wf\DockerWorkflowBundle\Event\Configuration\BuildInitEvent;
use Wf\DockerWorkflowBundle\Event\Configuration\RegisterEvent;
use Wf\DockerWorkflowBundle\Event\Configuration\VerboseInfoEvent;
use Wf\DockerWorkflowBundle\Event\ConfigurationEvents;
use Wf\DockerWorkflowBundle\Event\RegisterEventListenersInterface;
use Wf\DockerWorkflowBundle\Event\SkeletonBuild\DumpFileEvent;
use Wf\DockerWorkflowBundle\Event\SkeletonBuildBaseEvents;
use Wf\DockerWorkflowBundle\Exception\SkipRecipeException;
use Wf\DockerWorkflowBundle\Recipes\BaseRecipe as AncestorBaseRecipe;
use Wf\DockerWorkflowBundle\Recipes\CreateBaseRecipe\Recipe;
use Wf\DockerWorkflowBundle\Recipes\HiddenRecipe;
use Wf\DockerWorkflowBundle\Skeleton\BuilderTrait;
use Wf\DockerWorkflowBundle\Skeleton\FileType\SkeletonFile;

class Builder
{
    use BuilderTrait;

    /**
     * @var RecipeManager
     */
    protected $recipeManager;

    /**
     * The directory name/relative path, where we generate the files
     *
     * @var string
     */
    protected $targetDirectoryName;

    /**
     * Builder constructor.
     *
     * @param Filesystem               $fileSystem
     * @param RecipeManager            $recipeManager
     * @param EventDispatcherInterface $eventDispatcher
     */
    public function __construct(Filesystem $fileSystem, RecipeManager $recipeManager, EventDispatcherInterface $eventDispatcher)
    {
        $this->fileSystem = $fileSystem;
        $this->recipeManager = $recipeManager;
        $this->eventDispatcher = $eventDispatcher;
    }

    /**
     * @return string
     *
     * @codeCoverageIgnore Simple getter
     */
    public function getTargetDirectoryName(): string
    {
        return $this->targetDirectoryName;
    }

    /**
     * @param string $targetDirectoryName
     *
     * @return $this
     */
    public function setTargetDirectoryName(string $targetDirectoryName): self
    {
        $this->targetDirectoryName = $targetDirectoryName;

        return $this;
    }

    /**
     * @param array  $config
     * @param string $projectPath
     * @param string $configHash
     *
     * @throws \Wf\DockerWorkflowBundle\Exception\MissingRecipeException
     * @throws \Exception
     * @throws \ReflectionException
     * @throws \Twig_Error_Loader
     * @throws \Twig_Error_Runtime
     * @throws \Twig_Error_Syntax
     */
    public function build(array $config, string $projectPath, string $configHash): void
    {
        if (!$this->targetDirectoryName) {
            throw new \InvalidArgumentException('You have to call first the `setTargetDirectoryName` function!');
        }

        // INIT
        $this->initEventListeners($projectPath);
        if (\array_key_exists('recipes', $config)) {
            foreach ($config['recipes'] as $recipeName => $recipeConfig) {
                $this->addRecipeEventListeners($projectPath, $recipeName, $recipeConfig, $config);
            }
        }
        $initEvent = new BuildInitEvent($config, $projectPath, $this->targetDirectoryName, $configHash);
        $this->eventDispatcher->dispatch($initEvent, ConfigurationEvents::BUILD_INIT);
        $initEvent->setConfig($this->configReplaceParameters($initEvent->getConfig(), $initEvent->getParameters()));
        // Init the directory structure
        $this->initDirectoryStructure($initEvent);
        $config = $initEvent->getConfig();

        // PRE recipes
        $registerEventPre = new RegisterEvent($projectPath, $config);
        $this->eventDispatcher->dispatch($registerEventPre, ConfigurationEvents::REGISTER_EVENT_PREBUILD);
        foreach ($registerEventPre->getRecipes() as $recipe) {
            $this->buildRecipe($projectPath, $recipe->getName(), $config, $config);
        }

        // PUBLIC RECIPES
        if (\array_key_exists('recipes', $config)) {
            foreach ($config['recipes'] as $recipeName => $recipeConfig) {
                $this->buildRecipe($projectPath, $recipeName, $recipeConfig, $config);
            }
        }

        // POST recipes
        $registerEventPost = new RegisterEvent($projectPath, $config);
        $this->eventDispatcher->dispatch($registerEventPost, ConfigurationEvents::REGISTER_EVENT_POSTBUILD);
        foreach ($registerEventPost->getRecipes() as $recipe) {
            $this->buildRecipe($projectPath, $recipe->getName(), $config, $config);
        }
    }

    /**
     * Replace parameters in the values of $config.
     *
     * <code>
     *  %wf.target_directory%/README.md --> .wf/README.md
     * </code>
     *
     * @param mixed $config
     * @param array $parameters
     *
     * @return mixed
     */
    protected function configReplaceParameters($config, array $parameters)
    {
        if (\is_array($config)) {
            foreach ($config as $key => $value) {
                $config[$key] = $this->configReplaceParameters($value, $parameters);
            }
        } elseif (\is_string($config)) {
            return strtr($config, $parameters);
        }

        return $config;
    }

    /**
     * Create directories:
     *  - target
     *  - data
     * Or just clean up.
     *
     * @param BuildInitEvent $initEvent
     */
    protected function initDirectoryStructure(BuildInitEvent $initEvent): void
    {
        $config = $initEvent->getConfig();
        $fullTargetPath = $initEvent->getProjectPath() . '/' . $initEvent->getTargetDirectory();
        // Create or clean the target directory
        if (!$this->fileSystem->exists($fullTargetPath) || !is_dir($fullTargetPath)) {
            $this->fileSystem->mkdir($fullTargetPath);
            $this->verboseInfo(sprintf(
                '<info>The <comment>%s</comment> directory has been created</info>',
                $fullTargetPath
            ));
        } else {
            // If the filename or directoryname starts with dot, we keep it. Eg: .data directory
            $this->fileSystem->remove(Finder::create()->in($fullTargetPath)->depth(0));
            $this->verboseInfo(sprintf(
                '<info>The <comment>%s</comment> directory has been clean</info>',
                $fullTargetPath
            ));
        }

        $dataPath = $config['docker_data_dir'];
        // If it is an relative path
        if (!\in_array($dataPath[0], ['/', '~'])) {
            $dataPath = $initEvent->getProjectPath() . '/' . $dataPath;
        }
        // The $dataPath would be a symbolic link if you are using deployer. If it is a symbolic link, it can link to
        // "outside from docker" and it causes error! That's why you have to check, if it is an existing symbolic link,
        // then you have to skip the `mkdir` command!
        //
        // Host filestructure:
        // -------------------
        //
        //  [project_path]
        //      ├── current -> releases/28
        //      ├── release -> releases/29
        //      ├── releases
        //      │   ├── 28
        //      │   │   └── [...]
        //      │   │
        //      │   └── 29
        //      │       ├── .wf
        //      │       │   └── .data -> ../../../shared/.wf/.data
        //      │       │
        //      │       └── [...]
        //      │
        //      └── shared
        //          ├── .wf
        //          │   └── .data       <- Link to here, it is missing in docker!
        //          │       └── [...]
        //          │
        //          └── [...]
        //
        // Container filestructure:
        // -------------------
        //
        //  [project_path]
        //      ├── .wf
        //      │   └── .data -> ../../../shared/.wf/.data  <== Missing link target!
        //      │
        //      └── [...]
        //
        if ((!$this->fileSystem->exists($dataPath) || !is_dir($dataPath)) && !is_link($dataPath)) {
            $this->fileSystem->mkdir($dataPath);
        }
        $config['docker_data_dir'] = $dataPath;
        $initEvent->setConfig($config);
    }

    /**
     * We can create a recipe with EventSubscriberInterface that can handle events!
     *
     * @param string $projectPath
     * @param string $recipeName
     * @param array  $recipeConfig
     * @param array  $globalConfig
     *
     * @throws \Wf\DockerWorkflowBundle\Exception\MissingRecipeException
     */
    protected function addRecipeEventListeners(
        string $projectPath,
        string $recipeName,
        array $recipeConfig,
        array $globalConfig = []
    ) {
        $this->verboseInfo(sprintf(
            "\n<info>Register event listeners of <comment>%s</comment> recipe</info>",
            $recipeName
        ));

        /** @var AncestorBaseRecipe $recipe */
        $recipe = $this->recipeManager->getRecipe($recipeName);

        if ($recipe instanceof RegisterEventListenersInterface) {
            $recipe->registerEventListeners($this->eventDispatcher);
        }
    }

    /**
     * Build a recipe.
     *
     * @param string $projectPath
     * @param string $recipeName
     * @param array  $recipeConfig
     * @param array  $globalConfig
     *
     * @throws \Wf\DockerWorkflowBundle\Exception\MissingRecipeException
     * @throws \Exception
     * @throws \ReflectionException
     * @throws \Twig_Error_Loader
     * @throws \Twig_Error_Runtime
     * @throws \Twig_Error_Syntax
     */
    protected function buildRecipe(string $projectPath, string $recipeName, array $recipeConfig, array $globalConfig = []): void
    {
        $this->verboseInfo(sprintf(
            "\n<info>Starting build <comment>%s</comment> recipe</info>",
            $recipeName
        ));
        $this->verboseInfo(['config' => $recipeConfig]);

        try {
            /** @var AncestorBaseRecipe $recipe */
            $recipe = $this->recipeManager->getRecipe($recipeName);

            /** @var SkeletonFile[] $skeletonFiles */
            $skeletonFiles = $recipe->build($projectPath, $recipeConfig, $globalConfig);
            $this->fixFilePath($projectPath, $recipe, $skeletonFiles);

            $this->dumpSkeletonFiles($skeletonFiles);
        } catch (SkipRecipeException $e) {
            // do nothing
            $this->verboseInfo(sprintf('<comment>Skip the <options=underscore>%s</> recipe</comment>', $recipeName));
        }
    }

    /**
     * @param $projectPath
     * @param AncestorBaseRecipe $recipe
     * @param SkeletonFile[]     $skeletonFiles
     */
    protected function fixFilePath(string $projectPath, AncestorBaseRecipe $recipe, array $skeletonFiles): void
    {
        foreach ($skeletonFiles as $skeletonFile) {
            $relativeTargetPath = sprintf(
                implode(\DIRECTORY_SEPARATOR, ['%s', '%s', '%s']),
                $this->targetDirectoryName,
                $recipe->getDirectoryName(),
                $skeletonFile->getRelativePath()
            );
            $skeletonFile->setRelativePath($relativeTargetPath);
            $skeletonFile->move($projectPath);
        }
    }

    /**
     * Print verbose informations. The $info may be array or string.
     *
     * @param string|array|null $info
     */
    protected function verboseInfo($info): void
    {
        $this->eventDispatcher->dispatch(new VerboseInfoEvent($info), ConfigurationEvents::VERBOSE_INFO);
    }

    /**
     * @param $projectPath
     *
     * @throws \Wf\DockerWorkflowBundle\Exception\MissingRecipeException
     */
    protected function initEventListeners(string $projectPath): void
    {
        $this->eventDispatcher->addListener(
            SkeletonBuildBaseEvents::AFTER_DUMP_FILE,
            [$this, 'fileVerboseInfo'],
            -999
        );
        // Register hidden recipe listeners
        $recipes = $this->recipeManager->getRecipes();
        foreach ($recipes as $recipe) {
            if ($recipe instanceof HiddenRecipe) {
                $this->addRecipeEventListeners($projectPath, $recipe->getName(), []);
            }
        }
    }

    public function fileVerboseInfo(DumpFileEvent $event): void
    {
        $skeletonFile = $event->getSkeletonFile();
        $this->verboseInfo(sprintf('    <comment>%-40s</comment> # %s', $skeletonFile->getRelativePath(), \get_class($skeletonFile)));
    }

    protected function eventBeforeDumpFile(DumpFileEvent $event): void
    {
    }

    protected function eventBeforeDumpTargetExists(DumpFileEvent $event): void
    {
    }

    protected function eventAfterDumpFile(DumpFileEvent $event): void
    {
    }

    protected function eventSkipDumpFile(DumpFileEvent $event)
    {
    }
}
