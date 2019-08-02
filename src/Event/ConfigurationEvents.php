<?php declare(strict_types=1);
/**
 * Created by IntelliJ IDEA.
 * User: chris
 * Date: 2018.03.27.
 * Time: 22:23
 */

namespace Wf\DockerWorkflowBundle\Event;

use Wf\DockerWorkflowBundle\Event\Configuration\BuildInitEvent;
use Wf\DockerWorkflowBundle\Event\Configuration\PreProcessConfigurationEvent;
use Wf\DockerWorkflowBundle\Event\Configuration\RegisterEvent;
use Wf\DockerWorkflowBundle\Event\Configuration\VerboseInfoEvent;

class ConfigurationEvents
{
    /**
     * @see PreProcessConfigurationEvent
     */
    const PRE_PROCESS_CONFIGURATION = 'wf.configuration.event.pre_process_configuration';

    /**
     * @see BuildInitEvent
     */
    const BUILD_INIT = 'wf.configuration.event.build_init';

    /**
     * @see RegisterEvent
     */
    const REGISTER_EVENT_PREBUILD = 'wf.configuration.event.register.prebuild';
    const REGISTER_EVENT_POSTBUILD = 'wf.configuration.event.register.postbuild';

    /**
     * @see VerboseInfoEvent
     */
    const VERBOSE_INFO = 'wf.configuration.event.verbose_info';
}
