<?php declare(strict_types=1);
/**
 * Created by IntelliJ IDEA.
 * User: chris
 * Date: 2018.03.24.
 * Time: 22:01
 */

namespace Wf\DockerWorkflowBundle\Skeleton\FileType;

class ExecutableSkeletonFile extends SkeletonFile
{
    /**
     * @var int
     */
    protected $permission = 0755;

    /**
     * @return int
     */
    public function getPermission(): int
    {
        return $this->baseFileInfo->isExecutable() ? $this->baseFileInfo->getPerms() : $this->permission;
    }

    /**
     * @param int $permission
     *
     * @return $this
     *
     * @codeCoverageIgnore Simple setter
     */
    public function setPermission(int $permission): self
    {
        $this->permission = $permission;

        return $this;
    }
}
