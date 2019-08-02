<?php declare(strict_types=1);
/**
 * Created by IntelliJ IDEA.
 * User: chris
 * Date: 2018.12.28.
 * Time: 12:52
 */

namespace Wf\DockerWorkflowBundle\Tests\Dummy\Recipes\Hidden;

use Wf\DockerWorkflowBundle\Recipes\HiddenRecipe as BaseHiddenRecipe;

class HiddenRecipe extends BaseHiddenRecipe
{
    public function getName(): string
    {
        return 'hidden';
    }
}
