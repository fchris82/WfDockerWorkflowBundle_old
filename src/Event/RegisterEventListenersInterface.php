<?php declare(strict_types=1);
/**
 * Created by IntelliJ IDEA.
 * User: chris
 * Date: 2018.11.28.
 * Time: 11:26
 */

namespace Wf\DockerWorkflowBundle\Event;

use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;

/**
 * Interface RegisterEventListenersInterface
 *
 * We want to register some recipes, but only that what we are using. So the EventSubscriberInterface isn't good for us
 * for this situations. The solution is this interface.
 */
interface RegisterEventListenersInterface
{
    public function registerEventListeners(EventDispatcherInterface $eventDispatcher): void;
}
