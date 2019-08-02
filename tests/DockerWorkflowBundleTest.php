<?php declare(strict_types=1);
/**
 * Created by IntelliJ IDEA.
 * User: chris
 * Date: 2018.12.27.
 * Time: 17:03
 */

namespace Docker\WorkflowBundle\Tests;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Docker\WorkflowBundle\DependencyInjection\Compiler\CollectRecipesPass;
use Docker\WorkflowBundle\DependencyInjection\Compiler\CollectWizardsPass;
use Docker\WorkflowBundle\Recipes\BaseRecipe;
use Docker\WorkflowBundle\DockerWorkflowBundle;
use Docker\WorkflowBundle\Wizard\WizardInterface;

class DockerWorkflowBundleTest extends \PHPUnit\Framework\TestCase
{
    public function testBuild()
    {
        $bundle = new DockerWorkflowBundle();
        $containerBuilder = new ContainerBuilder();
        $bundle->build($containerBuilder);

        $autoConfiguredInstanceof = $containerBuilder->getAutoconfiguredInstanceof();
        $this->assertEquals([
            BaseRecipe::class,
            WizardInterface::class,
        ], array_keys($autoConfiguredInstanceof));

        $tags = [];
        foreach ($autoConfiguredInstanceof as $interface => $childDefinition) {
            $tags = array_merge($tags, $childDefinition->getTags());
        }
        $this->assertEquals([
            'wf.recipe',
            'wf.wizard',
        ], array_keys($tags));

        $passConfig = $containerBuilder->getCompilerPassConfig();
        $passes = $passConfig->getBeforeOptimizationPasses();
        $passClasses = [];
        foreach ($passes as $compilerPass) {
            $passClasses[] = \get_class($compilerPass);
        }

        $this->assertTrue(\in_array(CollectRecipesPass::class, $passClasses));
        $this->assertTrue(\in_array(CollectWizardsPass::class, $passClasses));
    }
}
