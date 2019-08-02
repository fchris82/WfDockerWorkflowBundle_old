<?php declare(strict_types=1);
/**
 * Created by IntelliJ IDEA.
 * User: chris
 * Date: 2019.01.15.
 * Time: 13:39
 */

namespace Wf\DockerWorkflowBundle\Tests;

use Symfony\Component\Finder\Finder;
use Symfony\Component\Finder\SplFileInfo;
use Twig\Environment;
use Twig\Loader\FilesystemLoader;
use Wf\DockerWorkflowBundle\Skeleton\FileType\SkeletonFile;
use Wf\DockerWorkflowBundle\Skeleton\SkeletonHelper;

class SkeletonTestCase extends TestCase
{
    /**
     * @param array $classes
     *
     * @throws \ReflectionException
     * @throws \Twig\Error\LoaderError
     *
     * @return Environment
     */
    protected function buildTwig(array $classes = []): Environment
    {
        $twigLoader = new FilesystemLoader();
        foreach ($classes as $class) {
            $reflClass = new \ReflectionClass($class);
            $path = \dirname($reflClass->getFileName());
            $namespace = SkeletonHelper::generateTwigNamespace($reflClass);
            $twigLoader->addPath($path, $namespace);
        }
        $twig = new Environment($twigLoader);

        return $twig;
    }

    protected function assertSkeletonFilesEquals($directoryOrExpectedArray, $result)
    {
        if (\is_string($directoryOrExpectedArray)) {
            $this->assertEquals(
                $this->convertDirectoryToArray($directoryOrExpectedArray),
                $this->convertSkeletonFilesToArray($result)
            );
        } else {
            $this->assertEquals(
                $this->convertSkeletonFilesToArray($directoryOrExpectedArray),
                $this->convertSkeletonFilesToArray($result)
            );
        }
    }

    /**
     * @param array|SkeletonFile[] $skeletonFiles
     *
     * @return array
     */
    protected function convertSkeletonFilesToArray(array $skeletonFiles): array
    {
        $array = [];
        foreach ($skeletonFiles as $skeletonFile) {
            $contents = sprintf(
                "# Class: %s\n# HandleExisting: %s\n%s",
                \get_class($skeletonFile),
                $skeletonFile->getHandleExisting(),
                $skeletonFile->getContents()
            );
            $array[$skeletonFile->getRelativePathname()] = $this->cleanFileContents($contents);
        }

        ksort($array);

        return $array;
    }

    /**
     * @param string|string[] $directory
     *
     * @return array
     */
    protected function convertDirectoryToArray($directory): array
    {
        $files = Finder::create()
            ->files()
            ->in($directory)
            ->ignoreDotFiles(false)
        ;

        $array = [];
        /** @var SplFileInfo $file */
        foreach ($files as $file) {
            $array[$file->getRelativePathname()] = $this->cleanFileContents(file_get_contents($file->getPathname()));
        }

        return $array;
    }

    protected function cleanFileContents(string $contents): string
    {
        return str_replace(' ', '', $contents);
    }

    protected function buildSkeletonFile(string $skeletonClass, string $relativePathname, string $content): SkeletonFile
    {
        $fileinfo = new SplFileInfo($relativePathname, \dirname($relativePathname), $relativePathname);
        /** @var SkeletonFile $skeletonFile */
        $skeletonFile = new $skeletonClass($fileinfo);
        $skeletonFile->setContents($content);

        return $skeletonFile;
    }
}
