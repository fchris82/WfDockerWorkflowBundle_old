<?php declare(strict_types=1);
/**
 * Created by IntelliJ IDEA.
 * User: chris
 * Date: 2018.12.28.
 * Time: 14:17
 */

namespace Webtown\WorkflowBundle\Tests\Skeleton;

use Webtown\WorkflowBundle\Skeleton\SkeletonTwigFileInfo;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Finder\SplFileInfo;

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
                'WebtownWorkflowBundleTestsResourcesSkeleton',
                '@WebtownWorkflowBundleTestsResourcesSkeleton/skeletons/skeleton.twig',
            ],
            [
                __DIR__ . '/../Resources/Skeleton/skeleton.twig',
                'Skeleton',
                'WebtownWorkflowBundleTestsResourcesSkeleton',
                '@WebtownWorkflowBundleTestsResourcesSkeleton/skeletons/Skeleton/skeleton.twig',
            ],
        ];
    }
}
