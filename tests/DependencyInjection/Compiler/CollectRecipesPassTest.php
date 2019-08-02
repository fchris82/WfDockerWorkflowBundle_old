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
use Docker\WorkflowBundle\Configuration\RecipeManager;
use Docker\WorkflowBundle\DependencyInjection\Compiler\AbstractTwigSkeletonPass;
use Docker\WorkflowBundle\DependencyInjection\Compiler\CollectRecipesPass;
use Docker\WorkflowBundle\Tests\Resources\DependencyInjection\AbstractRecipe\AbstractRecipe;
use Docker\WorkflowBundle\Tests\Resources\DependencyInjection\OverriddenRecipe\OverriddenSkeletonsRecipe;
use Docker\WorkflowBundle\Tests\Resources\DependencyInjection\SimpleRecipe\SimpleRecipe;
use Docker\WorkflowBundle\DockerWorkflowBundle;

class CollectRecipesPassTest extends TestCase
{
    /**
     * @param array $recipes
     * @param array $managerMethodCalls
     * @param array $twigMethodCalls
     *
     * @throws \ReflectionException
     *
     * @dataProvider dpProcess
     */
    public function testProcess(array $recipes, array $managerMethodCalls, array $twigMethodCalls)
    {
        $containerBuilder = new ContainerBuilder(new ParameterBag([
            'twig.default_path' => realpath(__DIR__ . '/../../Resources/DependencyInjection/templates'),
        ]));
        // RecipeManager
        $recipeManagerDefinition = new Definition(RecipeManager::class);
        $containerBuilder->setDefinition(RecipeManager::class, $recipeManagerDefinition);
        // TwigLoader
        $twigLoaderDefinition = new Definition(FilesystemLoader::class);
        $containerBuilder->setDefinition(AbstractTwigSkeletonPass::DEFAULT_TWIG_LOADER, $twigLoaderDefinition);

        foreach ($recipes as $recipeClass) {
            $recipeDefinition = new Definition($recipeClass);
            $recipeDefinition->addTag(DockerWorkflowBundle::RECIPE_TAG);
            $containerBuilder->setDefinition($recipeClass, $recipeDefinition);
        }

        $pass = new CollectRecipesPass();
        $pass->process($containerBuilder);

        $this->assertEquals($managerMethodCalls, $recipeManagerDefinition->getMethodCalls());
        $this->assertEquals($twigMethodCalls, $twigLoaderDefinition->getMethodCalls());
    }

    public function dpProcess()
    {
        return [
            // No recipes
            [[], [], []],
            // Simple recipe, no overridden
            [
                [SimpleRecipe::class],
                [['addRecipe', [new Reference(SimpleRecipe::class)]]],
                [['addPath', [
                    realpath(__DIR__ . '/../../Resources/DependencyInjection/SimpleRecipe'),
                    'DockerWorkflowBundleTestsResourcesDependencyInjectionSimpleRecipeSimpleRecipe',
                ]]],
            ],
            // Abstract recipe, no registered to manager!
            [
                [AbstractRecipe::class],
                [], // empty!
                [['addPath', [
                    realpath(__DIR__ . '/../../Resources/DependencyInjection/AbstractRecipe'),
                    'DockerWorkflowBundleTestsResourcesDependencyInjectionAbstractRecipeAbstractRecipe',
                ]]],
            ],
            // Overridden skeletons recipe. There should be 2 twig add path
            [
                [OverriddenSkeletonsRecipe::class],
                [['addRecipe', [new Reference(OverriddenSkeletonsRecipe::class)]]],
                [
                    ['addPath', [
                        realpath(__DIR__ . '/../../Resources/DependencyInjection/templates/bundles/DockerWorkflowBundleTestsResourcesDependencyInjectionOverriddenRecipeOverriddenSkeletonsRecipe'),
                        'DockerWorkflowBundleTestsResourcesDependencyInjectionOverriddenRecipeOverriddenSkeletonsRecipe',
                    ]],
                    ['addPath', [
                        realpath(__DIR__ . '/../../Resources/DependencyInjection/OverriddenRecipe'),
                        'DockerWorkflowBundleTestsResourcesDependencyInjectionOverriddenRecipeOverriddenSkeletonsRecipe',
                    ]],
                ],
            ],
        ];
    }
}
