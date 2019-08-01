<?php declare(strict_types=1);
/**
 * Created by IntelliJ IDEA.
 * User: chris
 * Date: 2019.01.25.
 * Time: 15:30
 */

namespace Webtown\WorkflowBundle\Tests\Recipes\CreateBaseRecipe;

use Webtown\WorkflowBundle\Event\Configuration\BuildInitEvent;
use Webtown\WorkflowBundle\Event\ConfigurationEvents;
use Webtown\WorkflowBundle\Event\SkeletonBuild\DumpFileEvent;
use Webtown\WorkflowBundle\Event\SkeletonBuildBaseEvents;
use Webtown\WorkflowBundle\Recipes\CreateBaseRecipe\Recipe;
use Webtown\WorkflowBundle\Skeleton\FileType\DockerComposeSkeletonFile;
use Webtown\WorkflowBundle\Skeleton\FileType\ExecutableSkeletonFile;
use Webtown\WorkflowBundle\Skeleton\FileType\MakefileSkeletonFile;
use Webtown\WorkflowBundle\Skeleton\FileType\SkeletonFile;
use Webtown\WorkflowBundle\Test\Dummy\Filesystem;
use Webtown\WorkflowBundle\Tests\Dummy\Configuration\Environment;
use Webtown\WorkflowBundle\Tests\SkeletonTestCase;
use Mockery as m;
use Symfony\Component\EventDispatcher\EventDispatcher;
use Symfony\Component\Yaml\Yaml;

class RecipeTest extends SkeletonTestCase
{
    const BASE_PATH = __DIR__ . '/../../Resources/Recipes/CreateBaseRecipe/';

    public function testGetDirectoryName()
    {
        $recipe = new Recipe(
            m::mock(\Twig\Environment::class),
            m::mock(EventDispatcher::class),
            m::mock(Environment::class)
        );

        $this->assertEquals('', $recipe->getDirectoryName());
    }

    /**
     * @param string                $projectPath
     * @param array                 $env
     * @param array                 $recipeConfig
     * @param BuildInitEvent|null   $buildInitEvent
     * @param array|DumpFileEvent[] $dumpFileEvents
     * @param string                $resultDir
     *
     * @throws \Exception
     * @throws \ReflectionException
     * @throws \Twig_Error_Loader
     * @throws \Twig_Error_Runtime
     * @throws \Twig_Error_Syntax
     * @dataProvider dpBuild
     */
    public function testBuild(
        string $projectPath,
        array $env,
        array $recipeConfig,
        ?BuildInitEvent $buildInitEvent,
        array $dumpFileEvents,
        string $resultDir
    ) {
        $twig = $this->buildTwig([Recipe::class]);
        $eventDispatcher = new EventDispatcher();
        $environment = new Environment();
        $environment->setEnv($env);
        $recipe = new Recipe($twig, $eventDispatcher, $environment);
        $recipe->registerEventListeners($eventDispatcher);

        $globalConfig = Yaml::parseFile(static::BASE_PATH . $projectPath . '/.wf.yml');

        if ($buildInitEvent) {
            $eventDispatcher->dispatch($buildInitEvent, ConfigurationEvents::BUILD_INIT);
        }
        foreach ($dumpFileEvents as $dumpFileEvent) {
            $eventDispatcher->dispatch($dumpFileEvent, SkeletonBuildBaseEvents::AFTER_DUMP_FILE);
        }

        $skeletonFiles = $recipe->build(
            static::BASE_PATH . $projectPath,
            $recipeConfig,
            $globalConfig
        );

        $this->assertSkeletonFilesEquals(
            static::BASE_PATH . $resultDir,
            $skeletonFiles
        );
    }

    public function dpBuild(): array
    {
        $envContent = file_get_contents(static::BASE_PATH . 'env');
        preg_match_all('/^([A-Z_-]+)=(.*)$/m', $envContent, $matches, PREG_SET_ORDER);
        $defaultEnv = [];
        foreach ($matches as $match) {
            $defaultEnv[$match[1]] = $match[2];
        }

        return [
            [
                'in/minimal',
                $defaultEnv,
                [],
                null,
                [],
                'out/minimal',
            ],
            [
                'in/minimal',
                $defaultEnv,
                [],
                new BuildInitEvent([], '', '', 'testASDFG'),
                [
                    new DumpFileEvent($this, $this->buildSkeletonFile(
                        SkeletonFile::class,
                        'test1/skeleton.txt',
                        'Skeleton TXT'
                    ), m::mock(Filesystem::class)),
                    new DumpFileEvent($this, $this->buildSkeletonFile(
                        MakefileSkeletonFile::class,
                        'test1/makefile',
                        '# Makefile'
                    ), m::mock(Filesystem::class)),
                    new DumpFileEvent($this, $this->buildSkeletonFile(
                        MakefileSkeletonFile::class,
                        'test2/makefile',
                        '# Makefile'
                    ), m::mock(Filesystem::class)),
                    new DumpFileEvent($this, $this->buildSkeletonFile(
                        DockerComposeSkeletonFile::class,
                        'test2/docker-compose.yml',
                        '# docker compose yml'
                    ), m::mock(Filesystem::class)),
                    new DumpFileEvent($this, $this->buildSkeletonFile(
                        ExecutableSkeletonFile::class,
                        'test3/bin.sh',
                        '# bin.sh'
                    ), m::mock(Filesystem::class)),
                ],
                'out/simple',
            ],
        ];
    }
}
