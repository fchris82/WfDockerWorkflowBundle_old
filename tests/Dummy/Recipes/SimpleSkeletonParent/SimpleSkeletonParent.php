<?php declare(strict_types=1);
/**
 * Created by IntelliJ IDEA.
 * User: chris
 * Date: 2019.01.18.
 * Time: 16:35
 */

namespace Wf\DockerWorkflowBundle\Tests\Dummy\Recipes\SimpleSkeletonParent;

use Wf\DockerWorkflowBundle\Recipes\AbstractTemplateRecipe;
use Wf\DockerWorkflowBundle\Recipes\BaseRecipe;

class SimpleSkeletonParent extends BaseRecipe implements AbstractTemplateRecipe
{
    public function getName(): string
    {
        return 'simple_parent';
    }
}
