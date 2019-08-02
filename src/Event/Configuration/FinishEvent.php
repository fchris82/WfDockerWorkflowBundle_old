<?php declare(strict_types=1);
/**
 * Created by IntelliJ IDEA.
 * User: chris
 * Date: 2018.03.28.
 * Time: 19:22
 */

namespace Wf\DockerWorkflowBundle\Event\Configuration;

use Symfony\Component\Filesystem\Filesystem;
use Symfony\Contracts\EventDispatcher\Event;

class FinishEvent extends Event
{
    /**
     * @var Filesystem
     */
    protected $fileSystem;

    /**
     * FinishEvent constructor.
     *
     * @param Filesystem $fileSystem
     *
     * @codeCoverageIgnore Simple setter
     */
    public function __construct(Filesystem $fileSystem)
    {
        $this->fileSystem = $fileSystem;
    }

    /**
     * @return Filesystem
     *
     * @codeCoverageIgnore Simple getter
     */
    public function getFileSystem(): Filesystem
    {
        return $this->fileSystem;
    }
}
