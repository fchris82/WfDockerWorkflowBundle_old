<?php declare(strict_types=1);
/**
 * Created by IntelliJ IDEA.
 * User: chris
 * Date: 2018.03.26.
 * Time: 17:08
 */

namespace Webtown\WorkflowBundle\Recipes;

use Webtown\WorkflowBundle\Exception\RecipeHasNotConfigurationException;
use Symfony\Component\Config\Definition\Builder\NodeDefinition;

abstract class HiddenRecipe extends BaseRecipe
{
    /**
     * @deprecated We don't call it if the recipe is based on HiddenRecipe
     */
    public function getConfig(): NodeDefinition
    {
        throw new RecipeHasNotConfigurationException('The hidden recipes don\'t have and don\'t need config!');
    }
}
