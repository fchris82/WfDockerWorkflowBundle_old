<?php declare(strict_types=1);
/**
 * Created by IntelliJ IDEA.
 * User: chris
 * Date: 2018.03.26.
 * Time: 17:08
 */

namespace Wf\DockerWorkflowBundle\Recipes;

use Wf\DockerWorkflowBundle\Event\Configuration\RegisterEvent;
use Wf\DockerWorkflowBundle\Event\ConfigurationEvents;

abstract class SystemRecipe extends HiddenRecipe
{
    /**
     * @param RegisterEvent $event
     *
     * @see ConfigurationEvents::REGISTER_EVENT_PREBUILD
     */
    public function onWfConfigurationEventRegisterPrebuild(RegisterEvent $event): void
    {
        $event->addRecipe($this);
    }

    /**
     * @param RegisterEvent $event
     *
     * @see ConfigurationEvents::REGISTER_EVENT_POSTBUILD
     */
    public function onWfConfigurationEventRegisterPostbuild(RegisterEvent $event): void
    {
        $event->addRecipe($this);
    }
}
