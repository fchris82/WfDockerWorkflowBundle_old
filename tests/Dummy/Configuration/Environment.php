<?php declare(strict_types=1);
/**
 * Created by IntelliJ IDEA.
 * User: chris
 * Date: 2019.01.25.
 * Time: 16:10
 */

namespace Wf\DockerWorkflowBundle\Tests\Dummy\Configuration;

class Environment extends \Wf\DockerWorkflowBundle\Configuration\Environment
{
    public function setEnv($env): void
    {
        $this->env = $env;
    }
}
