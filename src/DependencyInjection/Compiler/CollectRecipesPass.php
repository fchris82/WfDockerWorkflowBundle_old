<?php declare(strict_types=1);

namespace Docker\WorkflowBundle\DependencyInjection\Compiler;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Reference;
use Docker\WorkflowBundle\Configuration\RecipeManager;
use Docker\WorkflowBundle\Recipes\AbstractTemplateRecipe;
use Docker\WorkflowBundle\DockerWorkflowBundle;

class CollectRecipesPass extends AbstractTwigSkeletonPass
{
    /**
     * Register all Recipe service.
     *
     * @param ContainerBuilder $container
     *
     * @throws \ReflectionException
     */
    public function process(ContainerBuilder $container): void
    {
        $definition = $container->getDefinition(RecipeManager::class);
        $twigFilesystemLoaderDefinition = $container->getDefinition(parent::DEFAULT_TWIG_LOADER);

        foreach ($container->findTaggedServiceIds(DockerWorkflowBundle::RECIPE_TAG) as $serviceId => $taggedService) {
            $serviceDefinition = $container->getDefinition($serviceId);
            if (!$this->isTheServiceAbstract($serviceDefinition)) {
                $definition->addMethodCall('addRecipe', [new Reference($serviceId)]);
            }

            $this->registerSkeletonService(
                $container->getParameter('twig.default_path'),
                $serviceDefinition,
                $twigFilesystemLoaderDefinition
            );
        }
    }

    /**
     * @param Definition $serviceDefinition
     *
     * @throws \ReflectionException
     *
     * @return bool
     */
    protected function isTheServiceAbstract(Definition $serviceDefinition): bool
    {
        $refClass = new \ReflectionClass($serviceDefinition->getClass());

        return \in_array(AbstractTemplateRecipe::class, $refClass->getInterfaceNames())
            && !\in_array(AbstractTemplateRecipe::class, $refClass->getParentClass()->getInterfaceNames());
    }
}
