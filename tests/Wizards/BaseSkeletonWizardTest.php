<?php declare(strict_types=1);
/**
 * Created by IntelliJ IDEA.
 * User: chris
 * Date: 2019.01.28.
 * Time: 14:06
 */

namespace Wf\DockerWorkflowBundle\Tests\Wizards;

use Mockery as m;
use Symfony\Component\EventDispatcher\EventDispatcher;
use Symfony\Component\Finder\SplFileInfo;
use Twig\Environment;
use Wf\DockerWorkflowBundle\Environment\Commander;
use Wf\DockerWorkflowBundle\Skeleton\FileType\SkeletonFile;
use Wf\DockerWorkflowBundle\Test\Dummy\Filesystem;
use Wf\DockerWorkflowBundle\Tests\Dummy\Environment\IoManager;
use Wf\DockerWorkflowBundle\Tests\Dummy\Wizards\BaseSkeletonWizard;
use Wf\DockerWorkflowBundle\Tests\SkeletonTestCase;

class BaseSkeletonWizardTest extends SkeletonTestCase
{
    public function tearDown(): void
    {
        m::close();
    }

//    public function testInitBuild()
//    {
//
//    }

    /**
     * @param string $workDir
     * @param string $checkFile
     * @param bool   $result
     *
     * @dataProvider dpIsBuilt
     */
    public function testIsBuilt(string $workDir, string $checkFile, bool $result)
    {
        $skeletonWizard = new BaseSkeletonWizard(
            m::mock(IoManager::class),
            m::mock(Commander::class),
            new EventDispatcher(),
            m::mock(Environment::class),
            new Filesystem($workDir)
        );
        $skeletonWizard->setBuiltCheckFile($checkFile);

        $this->assertEquals($result, $skeletonWizard->isBuilt($workDir));
    }

    public function dpIsBuilt(): array
    {
        return [
            [__DIR__ . '/../Resources/Wizards/isBuiltTest', 'test.txt', true],
            [__DIR__ . '/../Resources/Wizards/isBuiltTest', 'missing.txt', false],
        ];
    }

    public function testRunBuild()
    {
        $targetProjectDirectory = __DIR__;
        $ioManager = new IoManager();
        $skeletonWizard = new BaseSkeletonWizard(
            $ioManager,
            m::mock(Commander::class),
            new EventDispatcher(),
            m::mock(Environment::class),
            new Filesystem($targetProjectDirectory)
        );
        $skeletonWizard->setReadVariables([
            'test1' => 'string value',
            'test2' => ['array value'],
            'test3' => new \stdClass(),
        ]);

        $resultDirectory = $skeletonWizard->runBuild($targetProjectDirectory);

        $this->assertEquals($targetProjectDirectory, $resultDirectory);
        $this->assertEquals([
            BaseSkeletonWizard::class . '::init' => true,
            BaseSkeletonWizard::class . '::build' => true,
            BaseSkeletonWizard::class . '::cleanUp' => true,
        ], $skeletonWizard->getBuildWizardEvent()->getParameters());
    }

    /**
     * @param string $filename
     * @param bool   $result
     *
     * @dataProvider dpIsWfConfigYamlFile
     */
    public function testIsWfConfigYamlFile(string $filename, bool $result)
    {
        $skeletonFile = new SkeletonFile(new SplFileInfo($filename, '', $filename));
        $skeletonWizard = new BaseSkeletonWizard(
            m::mock(IoManager::class),
            m::mock(Commander::class),
            new EventDispatcher(),
            m::mock(Environment::class),
            m::mock(Filesystem::class)
        );
        $response = $this->executeProtectedMethod($skeletonWizard, 'isWfConfigYamlFile', [$skeletonFile]);
        $this->assertEquals($result, $response);
    }

    public function dpIsWfConfigYamlFile(): array
    {
        return [
            ['',                    false],
            ['etc',                 false],
            ['text.txt',            false],
            ['text.yml',            false],
            ['wf.yml',              false],
            ['wf.yaml',             false],
            ['.wf.yml',             true],
            ['.wf.yaml',            true],
            ['.wf.yml.dist',        true],
            ['.wf.yaml.dist',       true],
            ['.wf.extra.yml',       true],
            ['.wf.extra.yaml',      true],
            ['.wf.extra.yml.dist',  true],
            ['.wf.extra.yaml.dist', true],
            ['.wf.xml',             false],
        ];
    }
}
