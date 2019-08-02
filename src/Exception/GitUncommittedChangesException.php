<?php declare(strict_types=1);
/**
 * Created by IntelliJ IDEA.
 * User: chris
 * Date: 2017.09.08.
 * Time: 13:53.
 */

namespace Wf\DockerWorkflowBundle\Exception;

/**
 * Class GitUncommittedChangesException.
 *
 * Ez akkor váltódik ki, ha nem commitolt állapotot érzékel.
 */
class GitUncommittedChangesException extends \Exception
{
}
