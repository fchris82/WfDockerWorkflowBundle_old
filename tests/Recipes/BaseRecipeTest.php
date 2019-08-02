<?php declare(strict_types=1);
/**
 * Created by IntelliJ IDEA.
 * User: chris
 * Date: 2018.12.28.
 * Time: 13:52
 */

namespace Wf\DockerWorkflowBundle\Tests\Recipes;

use PHPUnit\Framework\TestCase;
use Symfony\Component\Config\Definition\Dumper\YamlReferenceDumper;
use Symfony\Component\EventDispatcher\EventDispatcher;
use Symfony\Component\Yaml\Yaml;
use Twig\Environment;
use Twig\Loader\FilesystemLoader;
use Wf\DockerWorkflowBundle\Skeleton\FileType\DockerComposeSkeletonFile;
use Wf\DockerWorkflowBundle\Skeleton\FileType\ExecutableSkeletonFile;
use Wf\DockerWorkflowBundle\Skeleton\FileType\MakefileSkeletonFile;
use Wf\DockerWorkflowBundle\Skeleton\FileType\SkeletonFile;
use Wf\DockerWorkflowBundle\Tests\Dummy\Recipes\Simple\SimpleRecipe;
use Wf\DockerWorkflowBundle\Tests\Dummy\Recipes\SimpleSkeletonParent\SimpleSkeletonParent;

class BaseRecipeTest extends TestCase
{
    public function testGetConfig()
    {
        $recipe = new SimpleRecipe(new Environment(new FilesystemLoader()), new EventDispatcher());
        $dumper = new YamlReferenceDumper();
        $rootNode = $recipe->getConfig();
        $ymlTree = $dumper->dumpNode($rootNode->getNode(true));
        $config = Yaml::parse($ymlTree);

        $this->assertEquals(['simple' => []], $config);
    }

    /**
     * @param string       $projectPath
     * @param array|string $recipeConfig
     * @param array        $globalConfig
     * @param array        $result
     *
     * @dataProvider dpGetSkeletonVars
     */
    public function testGetSkeletonVars(string $projectPath, $recipeConfig, array $globalConfig, array $result)
    {
        $recipe = new SimpleRecipe(new Environment(new FilesystemLoader()), new EventDispatcher());
        $response = $recipe->getSkeletonVars($projectPath, $recipeConfig, $globalConfig);

        $this->assertEquals($result, $response);
    }

    public function dpGetSkeletonVars(): array
    {
        return [
            // Simple 1
            ['', [], [], [
                'config' => [],
                'project_path' => '',
                'recipe_path' => '${BASE_DIRECTORY}/${PROJECT_DIR_NAME}/${WF_TARGET_DIRECTORY}/simple',
                'env' => $_ENV,
                'recipe' => 'simple',
            ]],
            // Simple 2
            ['project_path', ['config_key' => 'value'], ['global' => true], [
                'config' => ['global' => true],
                'project_path' => 'project_path',
                'recipe_path' => '${BASE_DIRECTORY}/${PROJECT_DIR_NAME}/${WF_TARGET_DIRECTORY}/simple',
                'env' => $_ENV,
                'config_key' => 'value',
                'recipe' => 'simple',
            ]],
        ];
    }

    /**
     * @param array  $parents
     * @param string $projectPath
     * @param array  $recipeConfig
     * @param array  $globalConfig
     * @param array  $result
     *
     * @throws \Exception
     * @throws \ReflectionException
     * @throws \Twig_Error_Loader
     * @throws \Twig_Error_Runtime
     * @throws \Twig_Error_Syntax
     *
     * @dataProvider dpBuild
     */
    public function testBuild(array $parents, string $projectPath, array $recipeConfig, array $globalConfig, array $result)
    {
        $twigLoader = new FilesystemLoader();
        $twigLoader->setPaths(
            [realpath(__DIR__ . '/../Dummy/Recipes/Simple')],
            'WfDockerWorkflowBundleTestsDummyRecipesSimpleSimpleRecipe'
        );
        $twigLoader->setPaths(
            [realpath(__DIR__ . '/../Dummy/Recipes/SimpleSkeletonParent')],
            'WfDockerWorkflowBundleTestsDummyRecipesSimpleSkeletonParentSimpleSkeletonParent'
        );
        $recipe = new SimpleRecipe(new Environment($twigLoader), new EventDispatcher());
        SimpleRecipe::setSkeletonParents($parents);
        $response = $recipe->build($projectPath, $recipeConfig, $globalConfig);

        $files = [];
        foreach ($response as $skeletonFile) {
            $files[$skeletonFile->getRelativePathname()] = \get_class($skeletonFile);
        }

        ksort($result);
        ksort($files);
        $this->assertEquals($result, $files);
    }

    public function dpBuild()
    {
        return [
            // Without parent
            [[], '', [], [], [
                'docker-compose.yml' => DockerComposeSkeletonFile::class,
                'executable.sh' => ExecutableSkeletonFile::class,
                'makefile' => MakefileSkeletonFile::class,
                'simple.file' => SkeletonFile::class,
            ]],
            // With parent
            [[SimpleSkeletonParent::class], '', [], [], [
                'docker-compose.yml' => DockerComposeSkeletonFile::class,
                'docker-compose.second.yml' => DockerComposeSkeletonFile::class,
                'executable.sh' => ExecutableSkeletonFile::class,
                'makefile' => MakefileSkeletonFile::class,
                'non-executable.sh' => ExecutableSkeletonFile::class,
                'simple.file' => SkeletonFile::class,
            ]],
        ];
    }

    /**
     * @param string $pattern
     * @param array  $items
     * @param string $result
     *
     * @dataProvider dpMakefileMultilineFormatter
     */
    public function testMakefileMultilineFormatter(string $pattern, array $items, string $result)
    {
        $recipe = new SimpleRecipe(new Environment(new FilesystemLoader()), new EventDispatcher());
        $response = $recipe->makefileFormat($pattern, $items);

        $this->assertEquals($result, $response);
    }

    public function dpMakefileMultilineFormatter()
    {
        return [
            ['', [], ''],
            ['include %s', ['1.mk'], 'include 1.mk'],
            [
                'include %s',
                ['1.mk', '2.mk', '3.mk', '4.mk'],
                // Backslash + \n!!
                "include 1.mk \\\n" .
                "        2.mk \\\n" .
                "        3.mk \\\n" .
                '        4.mk',
            ],
            [
                'FOO := %s',
                ['value1', 'value2', 'value3'],
                // Backslash + \n!!
                "FOO := value1 \\\n" .
                "       value2 \\\n" .
                '       value3',
            ],
        ];
    }
}
