<?php declare(strict_types=1);
/**
 * Created by IntelliJ IDEA.
 * User: chris
 * Date: 2018.12.28.
 * Time: 14:17
 */

namespace Wf\DockerWorkflowBundle\Tests\Skeleton;

use PHPUnit\Framework\TestCase;
use Symfony\Component\Finder\SplFileInfo;
use Wf\DockerWorkflowBundle\Skeleton\SkeletonTwigFileInfo;

class SkeletonTwigFileInfoTest extends TestCase
{
    /**
     * @param string $filePath
     * @param string $relativePath
     * @param string $twigNamespace
     * @param string $result
     *
     * @dataProvider getPaths
     */
    public function testGetTwigPath(string $filePath, string $relativePath, string $twigNamespace, string $result)
    {
        $relativePathName = trim($relativePath . '/' . basename($filePath), '/');
        $baseFileInfo = new SplFileInfo($filePath, $relativePath, $relativePathName);
        $skeletonFileInfo = SkeletonTwigFileInfo::create($baseFileInfo, $twigNamespace);

        $response = $skeletonFileInfo->getTwigPath();
        $this->assertEquals($result, $response);
    }

    public function getPaths(): array
    {
        return [
            [
                __DIR__ . '/../Resources/Skeleton/skeleton.twig',
                '',
                'DockerWorkflowBundleTestsResourcesSkeleton',
                '@DockerWorkflowBundleTestsResourcesSkeleton/skeletons/skeleton.twig',
            ],
            [
                __DIR__ . '/../Resources/Skeleton/skeleton.twig',
                'Skeleton',
                'DockerWorkflowBundleTestsResourcesSkeleton',
                '@DockerWorkflowBundleTestsResourcesSkeleton/skeletons/Skeleton/skeleton.twig',
            ],
        ];
    }
}
