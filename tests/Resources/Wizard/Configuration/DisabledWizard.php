<?php declare(strict_types=1);
/**
 * Created by IntelliJ IDEA.
 * User: chris
 * Date: 2019.01.16.
 * Time: 12:59
 */

namespace Webtown\WorkflowBundle\Tests\Resources\Wizard\Configuration;

use Webtown\WorkflowBundle\Event\Wizard\BuildWizardEvent;
use Webtown\WorkflowBundle\Wizards\BaseWizard;

class DisabledWizard extends BaseWizard
{
    public function __construct()
    {
        $this->ioManager = null;
        $this->commander = null;
        $this->eventDispatcher = null;
    }

    public function getDefaultName(): string
    {
        return 'Disabled Wizard';
    }

    protected function build(BuildWizardEvent $event): void
    {
        // do nothing
    }
}
