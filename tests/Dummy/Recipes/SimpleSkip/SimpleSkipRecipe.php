<?php declare(strict_types=1);
/**
 * Created by IntelliJ IDEA.
 * User: chris
 * Date: 2019.01.23.
 * Time: 12:18
 */

namespace Docker\WorkflowBundle\Tests\Dummy\Recipes\SimpleSkip;

use Docker\WorkflowBundle\Exception\SkipRecipeException;
use Docker\WorkflowBundle\Recipes\BaseRecipe;

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
