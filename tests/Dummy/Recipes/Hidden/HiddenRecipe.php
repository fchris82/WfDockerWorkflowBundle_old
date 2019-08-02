<?php declare(strict_types=1);
/**
 * Created by IntelliJ IDEA.
 * User: chris
 * Date: 2018.12.28.
 * Time: 12:52
 */

namespace Webtown\WorkflowBundle\Tests\Dummy\Recipes\Hidden;

use Webtown\WorkflowBundle\Recipes\HiddenRecipe as BaseHiddenRecipe;

class HiddenRecipe extends BaseHiddenRecipe
{
    public function getName(): string
    {
        return 'hidden';
    }
}