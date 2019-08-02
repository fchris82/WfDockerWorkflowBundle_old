<?php declare(strict_types=1);
/**
 * Created by IntelliJ IDEA.
 * User: chris
 * Date: 2019.01.18.
 * Time: 10:57
 */

namespace Docker\WorkflowBundle\Tests\Resources\DependencyInjection\OverriddenRecipe;

use Docker\WorkflowBundle\Recipes\BaseRecipe;

class OverriddenSkeletonsRecipe extends BaseRecipe
{
    public function getName(): string
    {
        return 'overridden';
    }
}
