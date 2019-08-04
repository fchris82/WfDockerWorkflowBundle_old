<?php declare(strict_types=1);
/**
 * Created by IntelliJ IDEA.
 * User: chris
 * Date: 2019.01.18.
 * Time: 17:18
 */

namespace Wf\DockerWorkflowBundle\Tests\Configuration;

use Mockery as m;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Config\Definition\Builder\ArrayNodeDefinition;
use Symfony\Component\EventDispatcher\EventDispatcher;
use Twig\Environment;
use Twig\Loader\FilesystemLoader;
use Wf\DockerWorkflowBundle\Configuration\Builder;
use Wf\DockerWorkflowBundle\Configuration\RecipeManager;
use Wf\DockerWorkflowBundle\Event\ConfigurationEvents;
use Wf\DockerWorkflowBundle\Event\SkeletonBuildBaseEvents;
use Wf\DockerWorkflowBundle\Test\Dummy\Filesystem;
use Wf\DockerWorkflowBundle\Tests\Dummy\Recipes\Configurable\ConfigurableRecipe;
use Wf\DockerWorkflowBundle\Tests\Dummy\Recipes\ConflictWithSimpleEventListener\ConflictWithSimpleEventListenerRecipe;
use Wf\DockerWorkflowBundle\Tests\Dummy\Recipes\SimpleEventListener\SimpleEventListenerRecipe;
use Wf\DockerWorkflowBundle\Tests\Dummy\Recipes\SimpleSkip\SimpleSkipRecipe;
use Wf\DockerWorkflowBundle\Tests\Dummy\Recipes\SimpleSkipFile\SimpleSkipFileRecipe;
use Wf\DockerWorkflowBundle\Tests\Dummy\Recipes\SystemRecipe\SystemRecipe;

class BuilderTest extends TestCase
{
    public function tearDown(): void
    {
        m::close();
    }

    public function testBuildException()
    {
        $this->expectException(\InvalidArgumentException::class);
        $eventDispatcher = new EventDispatcher();
        $filesystem = new Filesystem(__DIR__, 'alias');
        $recipeManager = new RecipeManager();
        $builder = new Builder($filesystem, $recipeManager, $eventDispatcher);
        $builder->build([], '', '');
    }

    /**
     * @param       $projectPath
     * @param array $preSystemRecipes
     * @param array $recipeClasses
     * @param array $postSystemRecipes
     * @param array $config
     * @param array $result
     *
     * @throws \Wf\DockerWorkflowBundle\Exception\MissingRecipeException
     * @throws \Exception
     * @throws \ReflectionException
     * @throws \Twig_Error_Loader
     * @throws \Twig_Error_Runtime
     * @throws \Twig_Error_Syntax
     *
     * @dataProvider dpBuild
     */
    public function testBuild(
        $projectPath,
        array $preSystemRecipes,
        array $recipeClasses,
        array $postSystemRecipes,
        array $config,
        array $result
    ) {
        $eventDispatcher = new EventDispatcher();
        $filesystem = new Filesystem($projectPath, 'alias');
        $recipeManager = new RecipeManager();
        $twigFileLoader = new FilesystemLoader();
        $twigFileLoader->setPaths(__DIR__ . '/../Dummy/Recipes/SimpleEventListener', 'WfDockerWorkflowBundleTestsDummyRecipesSimpleEventListenerSimpleEventListenerRecipe');
        $twigFileLoader->setPaths(__DIR__ . '/../Dummy/Recipes/SystemRecipe', 'WfDockerWorkflowBundleTestsDummyRecipesSystemRecipeSystemRecipe');
        $twigFileLoader->setPaths(__DIR__ . '/../Dummy/Recipes/SimpleSkipFile', 'WfDockerWorkflowBundleTestsDummyRecipesSimpleSkipFileSimpleSkipFileRecipe');
        $twig = new Environment($twigFileLoader);
        // Build recipes
        foreach ($preSystemRecipes as $recipeName => $configDefinition) {
            $preSystemRecipe = new SystemRecipe($recipeName, $configDefinition, $twig, $eventDispatcher);
            $recipeManager->addRecipe($preSystemRecipe);
            $eventDispatcher->addListener(
                ConfigurationEvents::REGISTER_EVENT_PREBUILD,
                [$preSystemRecipe, 'onWfConfigurationEventRegisterPrebuild']
            );
        }
        foreach ($postSystemRecipes as $recipeName => $configDefinition) {
            $postSystemRecipe = new SystemRecipe($recipeName, $configDefinition, $twig, $eventDispatcher);
            $recipeManager->addRecipe($postSystemRecipe);
            $eventDispatcher->addListener(
                ConfigurationEvents::REGISTER_EVENT_POSTBUILD,
                [$postSystemRecipe, 'onWfConfigurationEventRegisterPostbuild']
            );
        }

        foreach ($recipeClasses as $recipeClass) {
            switch ($recipeClass) {
                case ConflictWithSimpleEventListenerRecipe::class:
                    $recipe = new $recipeClass($filesystem, $twig, $eventDispatcher);
                    break;
                default:
                    $recipe = new $recipeClass($twig, $eventDispatcher);
            }
            $recipeManager->addRecipe($recipe);
        }
        $builder = new Builder($filesystem, $recipeManager, $eventDispatcher);
        $builder->setTargetDirectoryName('.wf');
        $builder->build($config, $projectPath, 'testConfigHash');

        $this->assertEquals($result, $filesystem->getContents());
    }

    public function dpBuild()
    {
        $simpleConfig = [
            'version' => [
                'base' => '2.0.0',
                'wf_minimum_version' => '2.1.1',
            ],
            'name' => 'testproject',
            'imports' => [],
            'docker_data_dir' => '%wf.target_directory%/.data',
        ];
        $simpleConfigWithRecipes = [
            'version' => [
                'base' => '2.0.0',
                'wf_minimum_version' => '2.1.1',
            ],
            'name' => 'testproject',
            'imports' => [],
            'docker_data_dir' => '%wf.target_directory%/.data',
            'recipes' => [
                'simple_event_listener' => [],
                'simple_skip' => [],
                'simple_skip_file' => [],
            ],
        ];
        $simpleConfigWithConflicts = [
            'version' => [
                'base' => '2.0.0',
                'wf_minimum_version' => '2.1.1',
            ],
            'name' => 'testproject',
            'imports' => [],
            'docker_data_dir' => '%wf.target_directory%/.data',
            'recipes' => [
                'conflict_with_simple_event_listener' => [],    // <-- this create a file which the SimpleEventListener wants to create later
                'simple_event_listener' => [],
                'simple_skip' => [],
                'simple_skip_file' => [],
            ],
        ];
        $testConfig = [
            'version' => [
                'base' => '2.0.0',
                'wf_minimum_version' => '2.1.1',
            ],
            'name' => 'testproject',
            'imports' => [],
            'docker_data_dir' => '%wf.target_directory%/.data',
            'pre' => null,
            'recipes' => [
                'configurable' => ['name' => 'configurable test recipe'],
            ],
            'post' => null,
        ];
        $baseDir = __DIR__ . '/../Resources/ConfigurationBuilder/';
        $preDefinition = new ArrayNodeDefinition('pre');
        $postDefinition = new ArrayNodeDefinition('post');

        return [
            [ // Simple test, starting with empty directory
                $baseDir . 'empty',         // $targetDirectory
                [],                         // $preSystemRecipes
                [],                         // $recipeClasses
                [],                         // $postSystemRecipes
                $simpleConfig,              // $config
                [                           // $result
                    'alias/.gitkeep'    => '',
                    'alias/.wf'         => Filesystem::DIRECTORY_ID,
                    'alias/.wf/.data'   => Filesystem::DIRECTORY_ID,
                ],
            ],
            [ // Simple test with recipes, starting with empty directory
                $baseDir . 'empty',         // $targetDirectory
                [],                         // $preSystemRecipes
                [                           // $recipeClasses
                    SimpleEventListenerRecipe::class,
                    SimpleSkipRecipe::class,
                    SimpleSkipFileRecipe::class,
                ],
                [],                         // $postSystemRecipes
                $simpleConfigWithRecipes,   // $config
                [                           // $result
                    'alias/.gitkeep'                                        => '',
                    'alias/.wf/simple_skip_file/keep.txt'                   => '',
                    'alias/.wf'                                             => Filesystem::DIRECTORY_ID,
                    'alias/.wf/.data'                                       => Filesystem::DIRECTORY_ID,
                    'alias/.wf/simple_event_listener/examples'              => Filesystem::DIRECTORY_ID,
                    'alias/.wf/simple_event_listener/keep.txt'              => '',
                    'alias/.wf/simple_event_listener/templates/README.md'   => "This is a README.md\n",
                    'alias/.wf/simple_event_listener/templates/test.sh'     => '',
                    'alias/.wf/simple_skip_file/examples'                   => Filesystem::DIRECTORY_ID,
                    'alias/.wf/simple_skip_file/templates/README.md'        => "This is a README.md\n",
                    'alias/.wf/simple_skip_file/templates/test.sh'          => '',
                ],
            ],
            [ // Simple test with recipes, starting with empty directory + testing the SkeletonBuildBaseEvents::BEFORE_DUMP_TARGET_EXISTS event
                $baseDir . 'empty',         // $targetDirectory
                [],                         // $preSystemRecipes
                [                           // $recipeClasses
                    ConflictWithSimpleEventListenerRecipe::class,
                    SimpleEventListenerRecipe::class,
                    SimpleSkipRecipe::class,
                    SimpleSkipFileRecipe::class,
                ],
                [],                         // $postSystemRecipes
                $simpleConfigWithConflicts, // $config
                [                           // $result
                    'alias/.gitkeep'                                                    => '',
                    'alias/.wf/simple_skip_file/keep.txt'                               => '',
                    'alias/.wf'                                                         => Filesystem::DIRECTORY_ID,
                    'alias/.wf/.data'                                                   => Filesystem::DIRECTORY_ID,
                    'alias/.wf/conflict_with_simple_event_listener/examples'            => '-- DIRECTORY --',
                    'alias/.wf/conflict_with_simple_event_listener/templates/README.md' => "This is a README.md\n",
                    'alias/.wf/conflict_with_simple_event_listener/templates/test.sh'   => '',
                    'alias/.wf/simple_event_listener/examples'                          => Filesystem::DIRECTORY_ID,
                    'alias/.wf/simple_event_listener/keep.txt'                          => '',
                    'alias/.wf/simple_event_listener/templates/README.md'               => "Existing\nThis is a README.md\n", // Existing file + append!
                    'alias/.wf/simple_event_listener/templates/test.sh'                 => "# Existing\n",                    // Existing + rename
                    'alias/.wf/simple_event_listener/templates/test.sh.new'             => '',                    // Existing + handle existing event (rename)
                    'alias/.wf/simple_skip_file/examples'                               => Filesystem::DIRECTORY_ID,
                    'alias/.wf/simple_skip_file/templates/README.md'                    => "This is a README.md\n",
                    'alias/.wf/simple_skip_file/templates/test.sh'                      => '',
                ],
            ],
            [ // Simple test with pre, post and a configurable recipe. Starting with empty directory.
                $baseDir . 'empty',         // $targetDirectory
                ['pre' => $preDefinition],  // $preSystemRecipes
                [                           // $recipeClasses
                    ConfigurableRecipe::class,
                ],
                ['post' => $postDefinition], // $postSystemRecipes
                $testConfig,                // $config
                [                           // $result
                    'alias/.gitkeep'            => '',
                    'alias/.wf/pre/.gitkeep'    => "testproject\n",
                    'alias/.wf/post/.gitkeep'   => "testproject\n",
                    'alias/.wf'                                     => Filesystem::DIRECTORY_ID,
                    'alias/.wf/.data'                               => Filesystem::DIRECTORY_ID,
                ],
            ],
            [ // Simple test without recipes, starting with existing directory. It should be delete all non hidden files and directories!
                $baseDir . 'existing',      // $targetDirectory
                [],                         // $preSystemRecipes
                [],                         // $recipeClasses
                [],                         // $postSystemRecipes
                $simpleConfig,              // $config
                [                           // $result
                    'alias/.gitkeep' => '',
                    'alias/.wf/.data/data.file' => '',
                ],
            ],
            [ // Simple test with pre, post and a configurable recipe. Starting with an existing directory.
                $baseDir . 'existing',      // $targetDirectory
                ['pre' => $preDefinition],  // $preSystemRecipes
                [                           // $recipeClasses
                    ConfigurableRecipe::class,
                ],
                ['post' => $postDefinition], // $postSystemRecipes
                $testConfig,                // $config
                [                           // $result
                    'alias/.gitkeep'            => '',
                    'alias/.wf/pre/.gitkeep'    => "testproject\n",
                    'alias/.wf/post/.gitkeep'   => "testproject\n",
                    'alias/.wf/.data/data.file' => '',
                ],
            ],
        ];
    }
}
