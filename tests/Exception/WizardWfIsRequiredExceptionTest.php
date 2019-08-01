<?php declare(strict_types=1);
/**
 * Created by IntelliJ IDEA.
 * User: chris
 * Date: 2019.01.29.
 * Time: 14:10
 */

namespace Webtown\WorkflowBundle\Tests\Exception;

use Webtown\WorkflowBundle\Environment\Commander;
use Webtown\WorkflowBundle\Environment\IoManager;
use Webtown\WorkflowBundle\Exception\WizardWfIsRequiredException;
use Webtown\WorkflowBundle\Wizards\BaseWizard;
use Mockery as m;
use PHPUnit\Framework\TestCase;
use Symfony\Component\EventDispatcher\EventDispatcher;

class WizardWfIsRequiredExceptionTest extends TestCase
{
    public function tearDown(): void
    {
        m::close();
    }

    /**
     * @param BaseWizard $wizard
     * @param string     $targetProjectPath
     * @param string     $message
     * @param string     $resultMessage
     *
     * @dataProvider dp__construct
     */
    public function test__construct(BaseWizard $wizard, string $targetProjectPath, string $message, string $resultMessage)
    {
        $exception = new WizardWfIsRequiredException($wizard, $targetProjectPath, $message);

        $this->assertEquals($wizard, $exception->getWizard());
        $this->assertEquals($targetProjectPath, $exception->getTargetProjectPath());
        $this->assertEquals($resultMessage, $exception->getMessage());
    }

    public function dp__construct(): array
    {
        $baseWizard = new \Webtown\WorkflowBundle\Tests\Dummy\Wizards\BaseWizard(
            m::mock(IoManager::class),
            m::mock(Commander::class),
            new EventDispatcher()
        );

        return [
            [clone $baseWizard, 'test', '', 'The `Webtown\WorkflowBundle\Tests\Dummy\Wizards\BaseWizard` wizard needs initialized and configured WF! (Target path: `test`)'],
            [clone $baseWizard, __DIR__, 'TEST: Something went wrong', 'TEST: Something went wrong'],
        ];
    }
}
