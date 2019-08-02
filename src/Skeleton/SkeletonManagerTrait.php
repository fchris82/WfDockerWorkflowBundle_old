<?php declare(strict_types=1);
/**
 * Created by IntelliJ IDEA.
 * User: chris
 * Date: 2018.11.12.
 * Time: 13:15
 */

namespace Wf\DockerWorkflowBundle\Skeleton;

use Symfony\Component\EventDispatcher\EventDispatcher;
use Symfony\Component\Finder\Finder;
use Symfony\Component\Finder\SplFileInfo;
use Twig\Environment;
use Wf\DockerWorkflowBundle\Event\SkeletonBuild\PostBuildSkeletonFileEvent;
use Wf\DockerWorkflowBundle\Event\SkeletonBuild\PostBuildSkeletonFilesEvent;
use Wf\DockerWorkflowBundle\Event\SkeletonBuild\PreBuildSkeletonFileEvent;
use Wf\DockerWorkflowBundle\Event\SkeletonBuild\PreBuildSkeletonFilesEvent;
use Wf\DockerWorkflowBundle\Event\SkeletonBuildBaseEvents;
use Wf\DockerWorkflowBundle\Exception\CircularReferenceException;
use Wf\DockerWorkflowBundle\Exception\SkipSkeletonFileException;
use Wf\DockerWorkflowBundle\Skeleton\FileType\SkeletonFile;

trait SkeletonManagerTrait
{
    /**
     * @var Environment
     */
    protected $twig;

    /**
     * @var EventDispatcher
     */
    protected $eventDispatcher;

    abstract protected function eventBeforeBuildFiles(PreBuildSkeletonFilesEvent $event): void;

    abstract protected function eventBeforeBuildFile(PreBuildSkeletonFileEvent $event): void;

    abstract protected function eventAfterBuildFile(PostBuildSkeletonFileEvent $event): void;

    abstract protected function eventAfterBuildFiles(PostBuildSkeletonFilesEvent $event): void;

    /**
     * @param $templateVars
     * @param array $buildConfig
     *
     * @throws \Exception
     * @throws \Twig_Error_Loader
     * @throws \Twig_Error_Runtime
     * @throws \Twig_Error_Syntax
     *
     * @return array|SkeletonFile[]
     */
    protected function buildSkeletonFiles(array $templateVars, array $buildConfig = []): array
    {
        $preBuildEvent = new PreBuildSkeletonFilesEvent($this, $templateVars, $buildConfig);
        $this->eventBeforeBuildFiles($preBuildEvent);
        $this->eventDispatcher->dispatch($preBuildEvent, SkeletonBuildBaseEvents::BEFORE_BUILD_FILES);

        $skeletonFiles = [];
        $baseSkeletonFileInfos = $preBuildEvent->getSkeletonFileInfos() ?: $this->getSkeletonFiles($buildConfig);
        $templateVars = $preBuildEvent->getSkeletonVars();
        $buildConfig = $preBuildEvent->getBuildConfig();

        /** @var SkeletonTwigFileInfo $skeletonFileInfo */
        foreach ($baseSkeletonFileInfos as $skeletonFileInfo) {
            try {
                $preEvent = new PreBuildSkeletonFileEvent($this, $skeletonFileInfo, $templateVars, $buildConfig);
                $this->eventBeforeBuildFile($preEvent);
                $this->eventDispatcher->dispatch($preEvent, SkeletonBuildBaseEvents::BEFORE_BUILD_FILE);
                $skeletonFile = $preEvent->getSkeletonFile()
                    ?: $this->buildSkeletonFile($preEvent->getSourceFileInfo(), $preEvent->getBuildConfig());
                $skeletonFile->setContents($this->parseTemplateFile(
                    $skeletonFileInfo,
                    $preEvent->getSkeletonVars()
                ));
                $postEvent = new PostBuildSkeletonFileEvent($this, $skeletonFile, $skeletonFileInfo, $preEvent->getSkeletonVars(), $preEvent->getBuildConfig());
                $this->eventDispatcher->dispatch($postEvent, SkeletonBuildBaseEvents::AFTER_BUILD_FILE);
                $this->eventAfterBuildFile($postEvent);
                $skeletonFiles[] = $postEvent->getSkeletonFile();
            } catch (SkipSkeletonFileException $exception) {
            }
        }

        $postBuildEvent = new PostBuildSkeletonFilesEvent($this, $skeletonFiles, $templateVars, $buildConfig);
        $this->eventDispatcher->dispatch($postBuildEvent, SkeletonBuildBaseEvents::AFTER_BUILD_FILES);
        $this->eventAfterBuildFiles($postBuildEvent);

        return $postBuildEvent->getSkeletonFiles();
    }

    /**
     * @param SplFileInfo $fileInfo
     * @param array       $buildRecipeConfig
     *
     * @throws SkipSkeletonFileException
     *
     * @return SkeletonFile
     */
    protected function buildSkeletonFile(SplFileInfo $fileInfo, array $buildRecipeConfig = []): SkeletonFile
    {
        return new SkeletonFile($fileInfo);
    }

    /**
     * @param SkeletonTwigFileInfo $templateFile
     * @param array                $templateVariables
     *
     * @throws \Twig\Error\LoaderError
     * @throws \Twig\Error\RuntimeError
     * @throws \Twig\Error\SyntaxError
     *
     * @return string
     */
    protected function parseTemplateFile(SkeletonTwigFileInfo $templateFile, array $templateVariables): string
    {
        return $this->twig->render($templateFile->getTwigPath(), $templateVariables);
    }

    /**
     * @param array $buildConfig
     *
     * @throws CircularReferenceException
     * @throws \ReflectionException
     *
     * @return array|SkeletonTwigFileInfo[]
     */
    protected function getSkeletonFiles(array $buildConfig): array
    {
        $pathsWithTwigNamespace = static::getSkeletonPaths($buildConfig);
        if (0 == \count($pathsWithTwigNamespace)) {
            return [];
        }

        $skeletonFiles = [];
        // We don't handle the overridden files here, just later. You can use the original or the new file in an event handler.
        foreach ($pathsWithTwigNamespace as $twigNamespace => $path) {
            $skeletonFinder = Finder::create()
                ->files()
                ->in($path)
                ->ignoreDotFiles(false);

            foreach ($skeletonFinder as $fileInfo) {
                $skeletonFiles[] = SkeletonTwigFileInfo::create($fileInfo, $twigNamespace);
            }
        }

        return $skeletonFiles;
    }

    /**
     * @param array $buildConfig
     *
     * @throws CircularReferenceException
     * @throws \ReflectionException
     *
     * @return array|string[]
     */
    public static function getSkeletonPaths(array $buildConfig = []): array
    {
        $skeletonPaths = [];
        foreach (static::getSkeletonParents() as $class) {
            $skeletonPaths = array_merge($skeletonPaths, $class::getSkeletonPaths($buildConfig));
        }
        $uniquePaths = array_unique($skeletonPaths);
        if ($uniquePaths != $skeletonPaths) {
            throw new CircularReferenceException('There are circular references in skeleton path.');
        }

        $refClass = new \ReflectionClass(static::class);
        $skeletonPath = \dirname($refClass->getFileName()) . \DIRECTORY_SEPARATOR . SkeletonHelper::SKELETONS_DIR;
        if (is_dir($skeletonPath)) {
            $skeletonPaths[SkeletonHelper::generateTwigNamespace($refClass)] = $skeletonPath;
        }

        return $skeletonPaths;
    }

    /**
     * @return array|string[]
     */
    public static function getSkeletonParents(): array
    {
        return [];
    }
}
