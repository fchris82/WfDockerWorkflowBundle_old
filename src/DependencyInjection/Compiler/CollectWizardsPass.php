<?php declare(strict_types=1);

namespace Wf\DockerWorkflowBundle\DependencyInjection\Compiler;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;
use Wf\DockerWorkflowBundle\WfDockerWorkflowBundle;
use Wf\DockerWorkflowBundle\Wizard\Manager;

class CollectWizardsPass extends AbstractTwigSkeletonPass
{
    /**
     * Register all Wizard service.
     *
     * @param ContainerBuilder $container
     *
     * @throws \ReflectionException
     */
    public function process(ContainerBuilder $container): void
    {
        $definition = $container->getDefinition(Manager::class);
        $twigFilesystemLoaderDefinition = $container->getDefinition(parent::DEFAULT_TWIG_LOADER);

        foreach ($container->findTaggedServiceIds(WfDockerWorkflowBundle::WIZARD_TAG) as $serviceId => $taggedService) {
            $serviceDefinition = $container->getDefinition($serviceId);
            $definition->addMethodCall('addWizard', [new Reference($serviceId)]);

            $this->registerSkeletonService(
                $container->getParameter('twig.default_path'),
                $serviceDefinition,
                $twigFilesystemLoaderDefinition
            );
        }
    }
}
