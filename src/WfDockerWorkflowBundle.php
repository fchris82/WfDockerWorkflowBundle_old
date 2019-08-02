<?php declare(strict_types=1);

namespace Wf\DockerWorkflowBundle;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Bundle\Bundle;
use Wf\DockerWorkflowBundle\DependencyInjection\Compiler\CollectRecipesPass;
use Wf\DockerWorkflowBundle\DependencyInjection\Compiler\CollectWizardsPass;
use Wf\DockerWorkflowBundle\Recipes\BaseRecipe;
use Wf\DockerWorkflowBundle\Wizard\WizardInterface;

class WfDockerWorkflowBundle extends Bundle
{
    const RECIPE_TAG = 'wf.recipe';
    const WIZARD_TAG = 'wf.wizard';

    public function build(ContainerBuilder $container)
    {
        // Register autoconfigurations
        $container->registerForAutoconfiguration(BaseRecipe::class)
            ->addTag(static::RECIPE_TAG);
        $container->registerForAutoconfiguration(WizardInterface::class)
            ->addTag(static::WIZARD_TAG);

        // Register collect passes
        $container->addCompilerPass(new CollectRecipesPass());
        $container->addCompilerPass(new CollectWizardsPass());
    }
}
