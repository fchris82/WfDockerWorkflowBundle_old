<?php declare(strict_types=1);
/**
 * Created by IntelliJ IDEA.
 * User: chris
 * Date: 2019.01.16.
 * Time: 12:59
 */

namespace Docker\WorkflowBundle\Tests\Resources\Wizard\Configuration;

use Docker\WorkflowBundle\Event\Wizard\BuildWizardEvent;
use Docker\WorkflowBundle\Wizards\BaseWizard;

class MissingWizard extends BaseWizard
{
    public function __construct()
    {
        $this->ioManager = null;
        $this->commander = null;
        $this->eventDispatcher = null;
    }

    public function getDefaultName(): string
    {
        return 'Missing Wizard';
    }

    protected function build(BuildWizardEvent $event): void
    {
        // do nothing
    }
}
