<?php declare(strict_types=1);
/**
 * Created by IntelliJ IDEA.
 * User: chris
 * Date: 2019.01.25.
 * Time: 15:30
 */

namespace Wf\DockerWorkflowBundle\Tests\Recipes\CreateBaseRecipe;

use Mockery as m;
use Symfony\Component\EventDispatcher\EventDispatcher;
use Symfony\Component\Yaml\Yaml;
use Wf\DockerWorkflowBundle\Event\Configuration\BuildInitEvent;
use Wf\DockerWorkflowBundle\Event\ConfigurationEvents;
use Wf\DockerWorkflowBundle\Event\SkeletonBuild\DumpFileEvent;
use Wf\DockerWorkflowBundle\Event\SkeletonBuildBaseEvents;
use Wf\DockerWorkflowBundle\Recipes\CreateBaseRecipe\Recipe;
use Wf\DockerWorkflowBundle\Skeleton\FileType\DockerComposeSkeletonFile;
use Wf\DockerWorkflowBundle\Skeleton\FileType\ExecutableSkeletonFile;
use Wf\DockerWorkflowBundle\Skeleton\FileType\MakefileSkeletonFile;
use Wf\DockerWorkflowBundle\Skeleton\FileType\SkeletonFile;
use Wf\DockerWorkflowBundle\Test\Dummy\Filesystem;
use Wf\DockerWorkflowBundle\Tests\Dummy\Configuration\Environment;
use Wf\DockerWorkflowBundle\Tests\SkeletonTestCase;

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
