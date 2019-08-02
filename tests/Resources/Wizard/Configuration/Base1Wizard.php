<?php declare(strict_types=1);
/**
 * Created by IntelliJ IDEA.
 * User: chris
 * Date: 2019.01.16.
 * Time: 12:58
 */

namespace Docker\WorkflowBundle\Tests\Resources\Wizard\Configuration;

use Docker\WorkflowBundle\Event\Wizard\BuildWizardEvent;
use Docker\WorkflowBundle\Wizards\BaseWizard;

class Base1Wizard extends BaseWizard
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
