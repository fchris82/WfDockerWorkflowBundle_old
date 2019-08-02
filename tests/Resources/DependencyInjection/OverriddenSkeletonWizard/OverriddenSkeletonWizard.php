<?php declare(strict_types=1);
/**
 * Created by IntelliJ IDEA.
 * User: chris
 * Date: 2019.01.18.
 * Time: 12:15
 */

namespace Docker\WorkflowBundle\Tests\Resources\DependencyInjection\OverriddenSkeletonWizard;

use Docker\WorkflowBundle\Event\Wizard\BuildWizardEvent;
use Docker\WorkflowBundle\Wizards\BaseSkeletonWizard;

class OverriddenSkeletonWizard extends BaseSkeletonWizard
{
    public function getDefaultName(): string
    {
        return 'Overridden skeleton wizard';
    }

    protected function build(BuildWizardEvent $event): void
    {
    }
}
