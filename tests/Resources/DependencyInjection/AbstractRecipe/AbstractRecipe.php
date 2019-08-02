<?php declare(strict_types=1);
/**
 * Created by IntelliJ IDEA.
 * User: chris
 * Date: 2019.01.18.
 * Time: 12:08
 */

namespace Docker\WorkflowBundle\Tests\Resources\DependencyInjection\AbstractRecipe;

use Docker\WorkflowBundle\Recipes\AbstractTemplateRecipe;
use Docker\WorkflowBundle\Recipes\BaseRecipe;

class AbstractRecipe extends BaseRecipe implements AbstractTemplateRecipe
{
    public function getName(): string
    {
        return 'Abstract recipe';
    }
}
