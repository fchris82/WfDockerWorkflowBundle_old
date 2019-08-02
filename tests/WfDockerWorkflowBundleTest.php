<?php declare(strict_types=1);
/**
 * Created by IntelliJ IDEA.
 * User: chris
 * Date: 2018.12.27.
 * Time: 17:03
 */

namespace Wf\DockerWorkflowBundle\Tests;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Wf\DockerWorkflowBundle\DependencyInjection\Compiler\CollectRecipesPass;
use Wf\DockerWorkflowBundle\DependencyInjection\Compiler\CollectWizardsPass;
use Wf\DockerWorkflowBundle\Recipes\BaseRecipe;
use Wf\DockerWorkflowBundle\WfDockerWorkflowBundle;
use Wf\DockerWorkflowBundle\Wizard\WizardInterface;

class WfDockerWorkflowBundleTest extends \PHPUnit\Framework\TestCase
{
    public function testBuild()
    {
        $bundle = new WfDockerWorkflowBundle();
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
