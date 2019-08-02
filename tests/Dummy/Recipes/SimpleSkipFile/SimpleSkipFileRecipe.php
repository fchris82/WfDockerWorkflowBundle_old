<?php declare(strict_types=1);
/**
 * Created by IntelliJ IDEA.
 * User: chris
 * Date: 2019.01.23.
 * Time: 12:18
 */

namespace Wf\DockerWorkflowBundle\Tests\Dummy\Recipes\SimpleSkipFile;

use Symfony\Component\Finder\SplFileInfo;
use Wf\DockerWorkflowBundle\Exception\SkipSkeletonFileException;
use Wf\DockerWorkflowBundle\Recipes\BaseRecipe;
use Wf\DockerWorkflowBundle\Skeleton\FileType\SkeletonFile;

class SimpleSkipFileRecipe extends BaseRecipe
{
    public function getName(): string
    {
        return 'simple_skip_file';
    }

    public function buildSkeletonFile(SplFileInfo $fileInfo, array $recipeConfig): SkeletonFile
    {
        if ('skip.txt' == $fileInfo->getFilename()) {
            throw new SkipSkeletonFileException();
        }

        return parent::buildSkeletonFile($fileInfo, $recipeConfig);
    }
}
