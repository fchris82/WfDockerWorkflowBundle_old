<?php declare(strict_types=1);
/**
 * Created by IntelliJ IDEA.
 * User: chris
 * Date: 2018.12.28.
 * Time: 14:33
 */

namespace Webtown\WorkflowBundle\Tests\Skeleton;

use Webtown\WorkflowBundle\Skeleton\TemplateTwigFileInfo;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Finder\SplFileInfo;

class TemplateTwigFileInfoTest extends TestCase
{
    /**
     * @param string      $filePath
     * @param string      $relativePath
     * @param string      $twigNamespace
     * @param string|null $directory
     * @param string      $result
     *
     * @dataProvider getPaths
     */
    public function testGetTwigPath(string $filePath, string $relativePath, string $twigNamespace, ?string $directory, string $result)
    {
        $relativePathName = trim($relativePath . '/' . basename($filePath), '/');
        $baseFileInfo = new SplFileInfo($filePath, $relativePath, $relativePathName);
        $templateTwigFileInfo = $directory
            ? new TemplateTwigFileInfo($baseFileInfo->getRelativePath(), $relativePath, $relativePathName, $twigNamespace, $directory)
            : new TemplateTwigFileInfo($baseFileInfo->getRelativePath(), $relativePath, $relativePathName, $twigNamespace)
        ;

        $response = $templateTwigFileInfo->getTwigPath();
        $this->assertEquals($result, $response);
    }

    public function getPaths(): array
    {
        return [
            [
                __DIR__ . '/../Resources/Skeleton/template.twig',
                '',
                'AppWebtownWorkflowBundleTestsResourcesSkeleton',
                null,
                '@AppWebtownWorkflowBundleTestsResourcesSkeleton/template/template.twig',
            ],
            [
                __DIR__ . '/../Resources/Skeleton/template.twig',
                'Skeleton',
                'AppWebtownWorkflowBundleTestsResourcesSkeleton',
                null,
                '@AppWebtownWorkflowBundleTestsResourcesSkeleton/template/Skeleton/template.twig',
            ],
            [
                __DIR__ . '/../Resources/Skeleton/template.twig',
                'Skeleton',
                'AppWebtownWorkflowBundleTestsResourcesSkeleton',
                'other_template',
                '@AppWebtownWorkflowBundleTestsResourcesSkeleton/other_template/Skeleton/template.twig',
            ],
        ];
    }
}
