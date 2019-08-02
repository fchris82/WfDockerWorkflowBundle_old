<?php declare(strict_types=1);
/**
 * Created by IntelliJ IDEA.
 * User: chris
 * Date: 2018.11.28.
 * Time: 10:44
 */

namespace Wf\DockerWorkflowBundle\Event;

use Symfony\Contracts\EventDispatcher\Event;

class NamespacedEvent extends Event
{
    /**
     * @var string
     */
    protected $namespace;

    /**
     * NamespacedEvent constructor.
     *
     * @param string|object $namespace
     */
    public function __construct($namespace)
    {
        if (\is_object($namespace)) {
            $namespace = \get_class($namespace);
        }
        $this->namespace = $namespace;
    }

    /**
     * @return string
     *
     * @codeCoverageIgnore Simple getter
     */
    public function getNamespace(): string
    {
        return $this->namespace;
    }

    /**
     * @param string|object $namespace
     *
     * @return bool
     *
     * @codeCoverageIgnore Simple compare
     */
    public function isNamespace($namespace): bool
    {
        if (\is_object($namespace)) {
            $namespace = \get_class($namespace);
        }

        return $this->namespace == $namespace;
    }
}
