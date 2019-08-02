<?php declare(strict_types=1);
/**
 * Created by IntelliJ IDEA.
 * User: chris
 * Date: 2018.03.26.
 * Time: 17:08
 */

namespace Docker\WorkflowBundle\Recipes;

use Docker\WorkflowBundle\Event\Configuration\RegisterEvent;
use Docker\WorkflowBundle\Event\ConfigurationEvents;

abstract class SystemRecipe extends HiddenRecipe
{
    /**
     * @param RegisterEvent $event
     *
     * @see ConfigurationEvents::REGISTER_EVENT_PREBUILD
     */
    public function onAppConfigurationEventRegisterPrebuild(RegisterEvent $event): void
    {
        $event->addRecipe($this);
    }

    /**
     * @param RegisterEvent $event
     *
     * @see ConfigurationEvents::REGISTER_EVENT_POSTBUILD
     */
    public function onAppConfigurationEventRegisterPostbuild(RegisterEvent $event): void
    {
        $event->addRecipe($this);
    }
}
