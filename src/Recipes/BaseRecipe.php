<?php declare(strict_types=1);
/**
 * Created by IntelliJ IDEA.
 * User: chris
 * Date: 2018.03.14.
 * Time: 22:00
 */

namespace Wf\DockerWorkflowBundle\Recipes;

use Symfony\Component\Config\Definition\Builder\ArrayNodeDefinition;
use Symfony\Component\Config\Definition\Builder\NodeDefinition;
use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Finder\SplFileInfo;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;
use Twig\Environment;
use Wf\DockerWorkflowBundle\Event\SkeletonBuild\PostBuildSkeletonFileEvent;
use Wf\DockerWorkflowBundle\Event\SkeletonBuild\PostBuildSkeletonFilesEvent;
use Wf\DockerWorkflowBundle\Event\SkeletonBuild\PreBuildSkeletonFileEvent;
use Wf\DockerWorkflowBundle\Event\SkeletonBuild\PreBuildSkeletonFilesEvent;
use Wf\DockerWorkflowBundle\Skeleton\FileType\DockerComposeSkeletonFile;
use Wf\DockerWorkflowBundle\Skeleton\FileType\ExecutableSkeletonFile;
use Wf\DockerWorkflowBundle\Skeleton\FileType\MakefileSkeletonFile;
use Wf\DockerWorkflowBundle\Skeleton\FileType\SkeletonFile;
use Wf\DockerWorkflowBundle\Skeleton\SkeletonManagerTrait;

abstract class BaseRecipe
{
    use SkeletonManagerTrait;

    /**
     * @var Environment
     */
    protected $twig;

    /**
     * BaseRecipe constructor.
     *
     * @param Environment              $twig
     * @param EventDispatcherInterface $eventDispatcher
     */
    public function __construct(Environment $twig, EventDispatcherInterface $eventDispatcher)
    {
        $this->twig = $twig;
        $this->eventDispatcher = $eventDispatcher;
    }

    abstract public function getName(): string;

    /**
     * @return ArrayNodeDefinition|NodeDefinition
     */
    public function getConfig(): NodeDefinition
    {
        $treeBuilder = new TreeBuilder($this->getName());

        return $treeBuilder->getRootNode();
    }

    /**
     * @param $projectPath
     * @param $recipeConfig
     * @param $globalConfig
     *
     * @throws \Exception
     * @throws \ReflectionException
     * @throws \Twig_Error_Loader
     * @throws \Twig_Error_Runtime
     * @throws \Twig_Error_Syntax
     *
     * @return array|SkeletonFile[]
     */
    public function build(string $projectPath, array $recipeConfig, array $globalConfig): array
    {
        $templateVars = $this->getSkeletonVars($projectPath, $recipeConfig, $globalConfig);

        return $this->buildSkeletonFiles($templateVars, $recipeConfig);
    }

    public function getSkeletonVars(string $projectPath, array $recipeConfig, array $globalConfig): array
    {
        if (\is_string($recipeConfig)) {
            $recipeConfig = ['value' => $recipeConfig];
        }

        return array_merge([
            'recipe' => $this->getName(),
            'config' => $globalConfig,
            'project_path' => $projectPath,
            'recipe_path' => '${BASE_DIRECTORY}/${PROJECT_DIR_NAME}/${WF_TARGET_DIRECTORY}/' . $this->getName(),
            'env' => $_ENV,
        ], $recipeConfig);
    }

    /**
     * @param SplFileInfo $fileInfo
     * @param $recipeConfig
     *
     * @return DockerComposeSkeletonFile|ExecutableSkeletonFile|MakefileSkeletonFile|SkeletonFile
     */
    protected function buildSkeletonFile(SplFileInfo $fileInfo, array $recipeConfig): SkeletonFile
    {
        if ($this->isDockerComposeFile($fileInfo)) {
            return new DockerComposeSkeletonFile($fileInfo);
        }
        if ($this->isMakefile($fileInfo)) {
            return new MakefileSkeletonFile($fileInfo);
        }
        if ($this->isExecutableFile($fileInfo)) {
            return new ExecutableSkeletonFile($fileInfo);
        }

        return new SkeletonFile($fileInfo);
    }

    protected function isMakefile(SplFileInfo $fileInfo): bool
    {
        return 'makefile' == $fileInfo->getFilename();
    }

    protected function isDockerComposeFile(SplFileInfo $fileInfo): bool
    {
        return 0 === strpos($fileInfo->getFilename(), 'docker-compose')
            && 'yml' == $fileInfo->getExtension();
    }

    protected function isExecutableFile(SplFileInfo $fileInfo): bool
    {
        return $fileInfo->isExecutable();
    }

    public function getDirectoryName(): string
    {
        return $this->getName();
    }

    /**
     * Formatting helper for makefiles, eg:
     * <code>
     *  # makefileMultilineFormatter('FOO := %s', ['value1', 'value2', 'value3'])
     *  FOO := value1 \
     *         value2 \
     *         value3
     * </code>
     *
     * @param string $pattern `printf` format pattern
     * @param array  $array
     *
     * @return string
     */
    protected function makefileMultilineFormatter($pattern, $array): string
    {
        $emptyPattern = sprintf($pattern, '');
        $glue = sprintf(" \\\n%s", str_repeat(' ', \strlen($emptyPattern)));

        return sprintf($pattern, implode($glue, $array));
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
}
