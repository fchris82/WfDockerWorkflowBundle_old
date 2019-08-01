<?php declare(strict_types=1);
/**
 * Created by IntelliJ IDEA.
 * User: chris
 * Date: 2019.01.18.
 * Time: 12:06
 */

namespace Webtown\WorkflowBundle\Tests\Resources\DependencyInjection\SimpleWizard;

use Webtown\WorkflowBundle\Event\Wizard\BuildWizardEvent;
use Webtown\WorkflowBundle\Wizards\BaseWizard;

class SimpleWizard extends BaseWizard
{
    public function getDefaultName(): string
    {
        return 'Simple wizard';
    }

    protected function build(BuildWizardEvent $event): void
    {
    }
}
