<?php declare(strict_types=1);
/**
 * Created by IntelliJ IDEA.
 * User: chris
 * Date: 2017.08.11.
 * Time: 15:54.
 */

namespace Wf\DockerWorkflowBundle\Wizard;

interface WizardInterface
{
    /**
     * @return string
     */
    public function getDefaultName(): string;

    public function getDefaultGroup(): string;

    public function getInfo(): string;

    public function isHidden(): bool;

    public function isBuilt(string $targetProjectDirectory): bool;

    /**
     * @param string $targetProjectDirectory
     *
     * @return string Get the $targetProjectDirectory, it may be changed
     */
    public function runBuild(string $targetProjectDirectory): string;
}
