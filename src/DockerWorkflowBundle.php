<?php declare(strict_types=1);

namespace Docker\WorkflowBundle;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Bundle\Bundle;
use Docker\WorkflowBundle\DependencyInjection\Compiler\CollectRecipesPass;
use Docker\WorkflowBundle\DependencyInjection\Compiler\CollectWizardsPass;
use Docker\WorkflowBundle\Recipes\BaseRecipe;
use Docker\WorkflowBundle\Wizard\WizardInterface;

class DockerWorkflowBundle extends Bundle
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
