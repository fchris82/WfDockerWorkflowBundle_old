<?php declare(strict_types=1);
/**
 * Created by IntelliJ IDEA.
 * User: chris
 * Date: 2019.01.17.
 * Time: 14:04
 */

namespace Wf\DockerWorkflowBundle\Tests\DependencyInjection\Compiler;

use PHPUnit\Framework\TestCase;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBag;
use Symfony\Component\DependencyInjection\Reference;
use Twig\Loader\FilesystemLoader;
use Wf\DockerWorkflowBundle\DependencyInjection\Compiler\AbstractTwigSkeletonPass;
use Wf\DockerWorkflowBundle\DependencyInjection\Compiler\CollectWizardsPass;
use Wf\DockerWorkflowBundle\Tests\Resources\DependencyInjection\OverriddenSkeletonWizard\OverriddenSkeletonWizard;
use Wf\DockerWorkflowBundle\Tests\Resources\DependencyInjection\SimpleSkeletonWizard\SimpleSkeletonWizard;
use Wf\DockerWorkflowBundle\Tests\Resources\DependencyInjection\SimpleWizard\SimpleWizard;
use Wf\DockerWorkflowBundle\WfDockerWorkflowBundle;
use Wf\DockerWorkflowBundle\Wizard\Manager;

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
            $wizardDefinition->addTag(WfDockerWorkflowBundle::WIZARD_TAG);
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
                    'WfDockerWorkflowBundleTestsResourcesDependencyInjectionSimpleWizardSimpleWizard',
                ]]],
            ],
            // Simple wizard with skeleton, no overridden
            [
                [SimpleSkeletonWizard::class],
                [['addWizard', [new Reference(SimpleSkeletonWizard::class)]]],
                [['addPath', [
                    realpath(__DIR__ . '/../../Resources/DependencyInjection/SimpleSkeletonWizard'),
                    'WfDockerWorkflowBundleTestsResourcesDependencyInjectionSimpleSkeletonWizardSimpleSkeletonWizard',
                ]]],
            ],
            // Overridden skeletons wizard. There should be 2 twig add path
            [
                [OverriddenSkeletonWizard::class],
                [['addWizard', [new Reference(OverriddenSkeletonWizard::class)]]],
                [
                    ['addPath', [
                        realpath(__DIR__ . '/../../Resources/DependencyInjection/templates/bundles/WfDockerWorkflowBundleTestsResourcesDependencyInjectionOverriddenSkeletonWizardOverriddenSkeletonWizard'),
                        'WfDockerWorkflowBundleTestsResourcesDependencyInjectionOverriddenSkeletonWizardOverriddenSkeletonWizard',
                    ]],
                    ['addPath', [
                        realpath(__DIR__ . '/../../Resources/DependencyInjection/OverriddenSkeletonWizard'),
                        'WfDockerWorkflowBundleTestsResourcesDependencyInjectionOverriddenSkeletonWizardOverriddenSkeletonWizard',
                    ]],
                ],
            ],
        ];
    }
}
