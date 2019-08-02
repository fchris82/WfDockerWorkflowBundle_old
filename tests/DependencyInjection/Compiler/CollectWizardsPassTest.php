<?php declare(strict_types=1);
/**
 * Created by IntelliJ IDEA.
 * User: chris
 * Date: 2019.01.17.
 * Time: 14:04
 */

namespace Docker\WorkflowBundle\Tests\DependencyInjection\Compiler;

use PHPUnit\Framework\TestCase;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBag;
use Symfony\Component\DependencyInjection\Reference;
use Twig\Loader\FilesystemLoader;
use Docker\WorkflowBundle\DependencyInjection\Compiler\AbstractTwigSkeletonPass;
use Docker\WorkflowBundle\DependencyInjection\Compiler\CollectWizardsPass;
use Docker\WorkflowBundle\Tests\Resources\DependencyInjection\OverriddenSkeletonWizard\OverriddenSkeletonWizard;
use Docker\WorkflowBundle\Tests\Resources\DependencyInjection\SimpleSkeletonWizard\SimpleSkeletonWizard;
use Docker\WorkflowBundle\Tests\Resources\DependencyInjection\SimpleWizard\SimpleWizard;
use Docker\WorkflowBundle\DockerWorkflowBundle;
use Docker\WorkflowBundle\Wizard\Manager;

class CollectWizardsPassTest extends TestCase
{
    /**
     * @param array $wizards
     * @param array $managerMethodCalls
     * @param array $twigMethodCalls
     *
     * @throws \ReflectionException
     *
     * @dataProvider dpProcess
     */
    public function testProcess(array $wizards, array $managerMethodCalls, array $twigMethodCalls)
    {
        $containerBuilder = new ContainerBuilder(new ParameterBag([
            'twig.default_path' => realpath(__DIR__ . '/../../Resources/DependencyInjection/templates'),
        ]));
        // WizardManager
        $wizardManagerDefinition = new Definition(Manager::class);
        $containerBuilder->setDefinition(Manager::class, $wizardManagerDefinition);
        // TwigLoader
        $twigLoaderDefinition = new Definition(FilesystemLoader::class);
        $containerBuilder->setDefinition(AbstractTwigSkeletonPass::DEFAULT_TWIG_LOADER, $twigLoaderDefinition);

        foreach ($wizards as $wizadClass) {
            $wizardDefinition = new Definition($wizadClass);
            $wizardDefinition->addTag(DockerWorkflowBundle::WIZARD_TAG);
            $containerBuilder->setDefinition($wizadClass, $wizardDefinition);
        }

        $pass = new CollectWizardsPass();
        $pass->process($containerBuilder);

        $this->assertEquals($managerMethodCalls, $wizardManagerDefinition->getMethodCalls());
        $this->assertEquals($twigMethodCalls, $twigLoaderDefinition->getMethodCalls());
    }

    public function dpProcess()
    {
        return [
            // No wizards
            [[], [], []],
            // Simple wizard, no skeleton. It doesn't matter that there is or there isn't skeletons path! We register the twig path.
            [
                [SimpleWizard::class],
                [['addWizard', [new Reference(SimpleWizard::class)]]],
                [['addPath', [
                    realpath(__DIR__ . '/../../Resources/DependencyInjection/SimpleWizard'),
                    'DockerWorkflowBundleTestsResourcesDependencyInjectionSimpleWizardSimpleWizard',
                ]]],
            ],
            // Simple wizard with skeleton, no overridden
            [
                [SimpleSkeletonWizard::class],
                [['addWizard', [new Reference(SimpleSkeletonWizard::class)]]],
                [['addPath', [
                    realpath(__DIR__ . '/../../Resources/DependencyInjection/SimpleSkeletonWizard'),
                    'DockerWorkflowBundleTestsResourcesDependencyInjectionSimpleSkeletonWizardSimpleSkeletonWizard',
                ]]],
            ],
            // Overridden skeletons wizard. There should be 2 twig add path
            [
                [OverriddenSkeletonWizard::class],
                [['addWizard', [new Reference(OverriddenSkeletonWizard::class)]]],
                [
                    ['addPath', [
                        realpath(__DIR__ . '/../../Resources/DependencyInjection/templates/bundles/DockerWorkflowBundleTestsResourcesDependencyInjectionOverriddenSkeletonWizardOverriddenSkeletonWizard'),
                        'DockerWorkflowBundleTestsResourcesDependencyInjectionOverriddenSkeletonWizardOverriddenSkeletonWizard',
                    ]],
                    ['addPath', [
                        realpath(__DIR__ . '/../../Resources/DependencyInjection/OverriddenSkeletonWizard'),
                        'DockerWorkflowBundleTestsResourcesDependencyInjectionOverriddenSkeletonWizardOverriddenSkeletonWizard',
                    ]],
                ],
            ],
        ];
    }
}
