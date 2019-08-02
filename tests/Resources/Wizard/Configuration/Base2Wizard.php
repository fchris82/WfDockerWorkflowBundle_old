<?php declare(strict_types=1);
/**
 * Created by IntelliJ IDEA.
 * User: chris
 * Date: 2019.01.16.
 * Time: 12:59
 */

namespace Wf\DockerWorkflowBundle\Tests\Resources\Wizard\Configuration;

use Wf\DockerWorkflowBundle\Event\Wizard\BuildWizardEvent;
use Wf\DockerWorkflowBundle\Wizards\BaseWizard;

class Base2Wizard extends BaseWizard
{
    public function __construct()
    {
        $this->ioManager = null;
        $this->commander = null;
        $this->eventDispatcher = null;
    }

    public function getDefaultName(): string
    {
        return 'Base 1 Wizard';
    }

    protected function build(BuildWizardEvent $event): void
    {
        // do nothing
    }
}
