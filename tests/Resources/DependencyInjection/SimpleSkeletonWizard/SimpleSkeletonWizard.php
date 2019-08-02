<?php declare(strict_types=1);
/**
 * Created by IntelliJ IDEA.
 * User: chris
 * Date: 2019.01.18.
 * Time: 12:06
 */

namespace Wf\DockerWorkflowBundle\Tests\Resources\DependencyInjection\SimpleSkeletonWizard;

use Wf\DockerWorkflowBundle\Event\Wizard\BuildWizardEvent;
use Wf\DockerWorkflowBundle\Wizards\BaseSkeletonWizard;

class SimpleSkeletonWizard extends BaseSkeletonWizard
{
    public function getDefaultName(): string
    {
        return 'Simple skeleton wizard';
    }

    protected function build(BuildWizardEvent $event): void
    {
    }
}
