<?php declare(strict_types=1);
/**
 * Created by IntelliJ IDEA.
 * User: chris
 * Date: 2018.11.13.
 * Time: 14:56
 */

namespace Wf\DockerWorkflowBundle\Wizard;

class ConfigurationItem
{
    /**
     * @var string
     */
    protected $class;

    /**
     * @var string
     */
    protected $name;

    /**
     * @var bool
     */
    protected $enabled;

    /**
     * @var string
     */
    protected $group;

    /**
     * @var int
     */
    protected $priority;

    /**
     * ConfigurationItem constructor.
     *
     * @param string|object $class
     * @param bool          $enabled
     * @param string        $group
     * @param int           $priority
     */
    public function __construct($class, string $name, bool $enabled = true, string $group = '', int $priority = 0)
    {
        $this->class = \is_object($class) ? \get_class($class) : $class;
        $this->name = $name;
        $this->enabled = $enabled;
        $this->group = $group;
        $this->priority = $priority;
    }

    /**
     * @return string
     *
     * @codeCoverageIgnore Simple getters and setters
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * @return string
     *
     * @codeCoverageIgnore Simple getters and setters
     */
    public function getClass(): string
    {
        return $this->class;
    }

    /**
     * @return bool
     *
     * @codeCoverageIgnore Simple getters and setters
     */
    public function isEnabled(): bool
    {
        return $this->enabled;
    }

    /**
     * @return string
     *
     * @codeCoverageIgnore Simple getters and setters
     */
    public function getGroup(): string
    {
        return $this->group;
    }

    /**
     * @return int
     *
     * @codeCoverageIgnore Simple getters and setters
     */
    public function getPriority(): int
    {
        return $this->priority;
    }

    /**
     * @param string $name
     *
     * @return $this
     *
     * @codeCoverageIgnore Simple getters and setters
     */
    public function setName(string $name)
    {
        $this->name = $name;

        return $this;
    }

    /**
     * @param bool $enabled
     *
     * @return $this
     *
     * @codeCoverageIgnore Simple getters and setters
     */
    public function setEnabled(bool $enabled)
    {
        $this->enabled = $enabled;

        return $this;
    }

    /**
     * @param string $group
     *
     * @return $this
     *
     * @codeCoverageIgnore Simple getters and setters
     */
    public function setGroup(string $group)
    {
        $this->group = $group;

        return $this;
    }

    /**
     * @param int $priority
     *
     * @return $this
     *
     * @codeCoverageIgnore Simple getters and setters
     */
    public function setPriority(int $priority)
    {
        $this->priority = $priority;

        return $this;
    }
}
