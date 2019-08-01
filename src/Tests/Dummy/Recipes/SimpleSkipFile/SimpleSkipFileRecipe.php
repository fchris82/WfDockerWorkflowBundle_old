<?php declare(strict_types=1);
/**
 * Created by IntelliJ IDEA.
 * User: chris
 * Date: 2019.01.23.
 * Time: 12:18
 */

namespace Webtown\WorkflowBundle\Tests\Dummy\Recipes\SimpleSkipFile;

use Webtown\WorkflowBundle\Exception\SkipSkeletonFileException;
use Webtown\WorkflowBundle\Recipes\BaseRecipe;
use Webtown\WorkflowBundle\Skeleton\FileType\SkeletonFile;
use Symfony\Component\Finder\SplFileInfo;

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
