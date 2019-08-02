<?php declare(strict_types=1);
/**
 * Created by IntelliJ IDEA.
 * User: chris
 * Date: 2018.11.30.
 * Time: 13:11
 */

namespace Wf\DockerWorkflowBundle\Environment;

class EnvParser
{
    /**
     * A /package/opt/webtown-workflow/symfony/docker-compose.yml fájlban lehet átadni paramétereket, amik
     * kellhetnek majd generálásoknál. Pl ORIGINAL_PWD .
     *
     * @param string      $name
     * @param string|null $default
     *
     * @return string|null
     *
     * @codeCoverageIgnore Simple getter
     */
    public function get(string $name, string $default = null): ?string
    {
        return \array_key_exists($name, $_ENV) ? $_ENV[$name] : $default;
    }
}
