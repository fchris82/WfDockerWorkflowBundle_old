<?php declare(strict_types=1);
/**
 * Created by IntelliJ IDEA.
 * User: chris
 * Date: 2019.01.18.
 * Time: 12:06
 */

namespace Wf\DockerWorkflowBundle\Tests\Resources\DependencyInjection\SimpleWizard;

use Wf\DockerWorkflowBundle\Event\Wizard\BuildWizardEvent;
use Wf\DockerWorkflowBundle\Wizards\BaseWizard;

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
