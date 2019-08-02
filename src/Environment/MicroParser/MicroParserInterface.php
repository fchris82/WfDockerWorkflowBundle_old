<?php declare(strict_types=1);
/**
 * Created by IntelliJ IDEA.
 * User: chris
 * Date: 2018.11.30.
 * Time: 12:35
 */

namespace Wf\DockerWorkflowBundle\Environment\MicroParser;

interface MicroParserInterface
{
    public function get(string $workingDirectory, string $key, $default = false);

    public function has(string $workingDirectory, string $key): bool;
}
