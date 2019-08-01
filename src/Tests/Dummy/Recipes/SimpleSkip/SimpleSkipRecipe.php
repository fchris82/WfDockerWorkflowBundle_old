<?php declare(strict_types=1);
/**
 * Created by IntelliJ IDEA.
 * User: chris
 * Date: 2019.01.23.
 * Time: 12:18
 */

namespace Webtown\WorkflowBundle\Tests\Dummy\Recipes\SimpleSkip;

use Webtown\WorkflowBundle\Exception\SkipRecipeException;
use Webtown\WorkflowBundle\Recipes\BaseRecipe;

class SimpleSkipRecipe extends BaseRecipe
{
    public function getName(): string
    {
        return 'simple_skip';
    }

    public function getSkeletonVars(string $projectPath, array $recipeConfig, array $globalConfig): array
    {
        throw new SkipRecipeException();
    }
}
