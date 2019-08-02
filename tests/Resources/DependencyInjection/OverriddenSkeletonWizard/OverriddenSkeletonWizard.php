<?php declare(strict_types=1);
/**
 * Created by IntelliJ IDEA.
 * User: chris
 * Date: 2019.01.18.
 * Time: 12:15
 */

namespace Wf\DockerWorkflowBundle\Tests\Resources\DependencyInjection\OverriddenSkeletonWizard;

use Wf\DockerWorkflowBundle\Event\Wizard\BuildWizardEvent;
use Wf\DockerWorkflowBundle\Wizards\BaseSkeletonWizard;

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
