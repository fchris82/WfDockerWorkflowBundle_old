<?php declare(strict_types=1);
/**
 * Created by IntelliJ IDEA.
 * User: chris
 * Date: 2019.01.22.
 * Time: 13:47
 */

namespace Webtown\WorkflowBundle\Tests\Dummy\Recipes\SystemWithoutConfigurationRecipe;

use Webtown\WorkflowBundle\Recipes\SystemRecipe;

class SystemWithoutConfigurationRecipe extends SystemRecipe
{
    public function getName(): string
    {
        return 'system_without_configuration';
    }
}
