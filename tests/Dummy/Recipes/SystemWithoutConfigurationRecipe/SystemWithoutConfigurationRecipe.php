<?php declare(strict_types=1);
/**
 * Created by IntelliJ IDEA.
 * User: chris
 * Date: 2019.01.22.
 * Time: 13:47
 */

namespace Wf\DockerWorkflowBundle\Tests\Dummy\Recipes\SystemWithoutConfigurationRecipe;

use Wf\DockerWorkflowBundle\Recipes\SystemRecipe;

class SystemWithoutConfigurationRecipe extends SystemRecipe
{
    public function getName(): string
    {
        return 'system_without_configuration';
    }
}
