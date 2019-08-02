<?php declare(strict_types=1);
/**
 * Created by IntelliJ IDEA.
 * User: chris
 * Date: 2019.01.28.
 * Time: 10:26
 */

namespace Wf\DockerWorkflowBundle\Tests\Wizards;

use Mockery as m;
use Symfony\Component\Console\Question\Question;
use Symfony\Component\EventDispatcher\EventDispatcher;
use Wf\DockerWorkflowBundle\Environment\Commander;
use Wf\DockerWorkflowBundle\Environment\IoManager;
use Wf\DockerWorkflowBundle\Exception\WizardHasAlreadyBuiltException;
use Wf\DockerWorkflowBundle\Exception\WizardSomethingIsRequiredException;
use Wf\DockerWorkflowBundle\Tests\Dummy\Environment\IoManager as IoManagerDummy;
use Wf\DockerWorkflowBundle\Tests\Dummy\Wizards\BaseWizard;
use Wf\DockerWorkflowBundle\Tests\TestCase;

class BaseWizardTest extends TestCase
{
    public function tearDown(): void
    {
        m::close();
    }

    public function testGetDefaults()
    {
        $baseWizard = new BaseWizard(
            m::mock(IoManager::class),
            m::mock(Commander::class),
            new EventDispatcher()
        );
        $targetProjectDirectory = __DIR__;

        $this->assertEquals('Wf\DockerWorkflowBundle\Tests\Dummy\Wizards\BaseWizard', $baseWizard->getDefaultName());
        $this->assertEquals('', $baseWizard->getDefaultGroup());
        $this->assertEquals('', $baseWizard->getInfo());
        $this->assertFalse($baseWizard->isHidden());
        $this->assertFalse($baseWizard->isBuilt($targetProjectDirectory));
        $this->assertTrue($baseWizard->checkRequires($targetProjectDirectory));
    }

    public function testAsk()
    {
        $ioManager = m::mock(IoManager::class);
        $baseWizard = new BaseWizard(
            $ioManager,
            m::mock(Commander::class),
            new EventDispatcher()
        );

        $question = new Question('Test?');
        $ioManager
            ->shouldReceive('ask')
            ->with($question)
            ->once()
            ->andReturn('Response')
        ;

        $response = $baseWizard->ask($question);
        $this->assertEquals('Response', $response);
    }

    public function testRunBuild1()
    {
        $baseWizard = new BaseWizard(
            m::mock(IoManager::class),
            m::mock(Commander::class),
            new EventDispatcher()
        );
        $targetProjectDirectory = __DIR__;

        $resultDirectory = $baseWizard->runBuild($targetProjectDirectory);

        $this->assertEquals($targetProjectDirectory, $resultDirectory);
        $this->assertEquals([
            BaseWizard::class . '::init' => true,
            BaseWizard::class . '::build' => true,
            BaseWizard::class . '::cleanUp' => true,
        ], $baseWizard->getBuildWizardEvent()->getParameters());
    }

    /**
     * @throws WizardHasAlreadyBuiltException
     */
    public function testRunBuild2()
    {
        $this->expectException(WizardHasAlreadyBuiltException::class);
        $baseWizard = new BaseWizard(
            m::mock(IoManager::class),
            m::mock(Commander::class),
            new EventDispatcher()
        );
        $targetProjectDirectory = __DIR__;

        // We want to throw a WizardHasAlreadyBuiltException
        $baseWizard->setIsBuilt(true);

        $baseWizard->runBuild($targetProjectDirectory);
    }

    public function testRunCmdInContainer()
    {
        $commander = m::mock(Commander::class);
        $baseWizard = new BaseWizard(
            m::mock(IoManager::class),
            $commander,
            new EventDispatcher()
        );
        $targetProjectDirectory = __DIR__;

        $cmd = 'ls -al';
        $commander
            ->shouldReceive('runCmdInContainer')
            ->once()
            ->with($cmd, 'fchris82/wf', '', $targetProjectDirectory)
            ->andReturn('You called runCmdInContainer')
        ;

        $result = $baseWizard->runCmdInContainer($cmd, $targetProjectDirectory);

        $this->assertEquals('You called runCmdInContainer', $result);
    }

    /**
     * @param BaseWizard $baseWizard
     * @param BaseWizard $calledWizard
     * @param array      $output
     *
     * @dataProvider dpCall
     */
    public function testCall(BaseWizard $baseWizard, BaseWizard $calledWizard, array $output)
    {
        $targetProjectDirectory = __DIR__;

        $this->executeProtectedMethod($baseWizard, 'call', [$targetProjectDirectory, $calledWizard]);
        /** @var IoManagerDummy $ioManager */
        $ioManager = $this->getProtectedProperty($baseWizard, 'ioManager');
        $this->assertEquals($output, $ioManager->getLog());
    }

    public function dpCall(): array
    {
        $ioManagerMock = new IoManagerDummy();
        $commanderMock = m::mock(Commander::class);
        $eventDispatcher = new EventDispatcher();
        $baseWizard = new BaseWizard($ioManagerMock, $commanderMock, $eventDispatcher);

        $simpleWizard = new BaseWizard($ioManagerMock, $commanderMock, $eventDispatcher);
        $builtWizard = new BaseWizard($ioManagerMock, $commanderMock, $eventDispatcher);
        $builtWizard->setIsBuilt(true);
        $hiddenWizard = new BaseWizard($ioManagerMock, $commanderMock, $eventDispatcher);
        $hiddenWizard->setIsHidden(true);
        $missingRequires = new BaseWizard($ioManagerMock, $commanderMock, $eventDispatcher);
        $missingRequires->setCheckRequires(new WizardSomethingIsRequiredException('Something is required!'));

        return [
            [$baseWizard, $simpleWizard, []],
            [$baseWizard, $builtWizard, []],
            [$baseWizard, $hiddenWizard, []],
            [$baseWizard, $missingRequires, ['Something is required!']],
        ];
    }
}
