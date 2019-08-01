<?php declare(strict_types=1);
/**
 * Created by IntelliJ IDEA.
 * User: chris
 * Date: 2018.03.27.
 * Time: 22:23
 */

namespace Webtown\WorkflowBundle\Event;

use Webtown\WorkflowBundle\Event\Configuration\BuildInitEvent;
use Webtown\WorkflowBundle\Event\Configuration\PreProcessConfigurationEvent;
use Webtown\WorkflowBundle\Event\Configuration\RegisterEvent;
use Webtown\WorkflowBundle\Event\Configuration\VerboseInfoEvent;

class ConfigurationEvents
{
    /**
     * @see PreProcessConfigurationEvent
     */
    const PRE_PROCESS_CONFIGURATION = 'app.configuration.event.pre_process_configuration';

    /**
     * @see BuildInitEvent
     */
    const BUILD_INIT = 'app.configuration.event.build_init';

    /**
     * @see RegisterEvent
     */
    const REGISTER_EVENT_PREBUILD = 'app.configuration.event.register.prebuild';
    const REGISTER_EVENT_POSTBUILD = 'app.configuration.event.register.postbuild';

    /**
     * @see VerboseInfoEvent
     */
    const VERBOSE_INFO = 'app.configuration.event.verbose_info';
}
