<?php declare(strict_types=1);
/**
 * Created by IntelliJ IDEA.
 * User: chris
 * Date: 2019.01.23.
 * Time: 12:18
 */

namespace Docker\WorkflowBundle\Tests\Dummy\Recipes\SimpleSkipFile;

use Symfony\Component\Finder\SplFileInfo;
use Docker\WorkflowBundle\Exception\SkipSkeletonFileException;
use Docker\WorkflowBundle\Recipes\BaseRecipe;
use Docker\WorkflowBundle\Skeleton\FileType\SkeletonFile;

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
