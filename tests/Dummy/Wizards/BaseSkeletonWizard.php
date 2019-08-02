<?php declare(strict_types=1);
/**
 * Created by IntelliJ IDEA.
 * User: chris
 * Date: 2019.01.28.
 * Time: 14:07
 */

namespace Wf\DockerWorkflowBundle\Tests\Dummy\Wizards;

use Wf\DockerWorkflowBundle\Event\Wizard\BuildWizardEvent;

class BaseSkeletonWizard extends \Wf\DockerWorkflowBundle\Wizards\BaseSkeletonWizard
{
    /**
     * @var BuildWizardEvent
     */
    private $event;

    /**
     * @var bool
     */
    private $isHidden;

    /**
     * @var string|null
     */
    private $builtCheckFile;

    /**
     * @var bool|\Exception
     */
    private $checkRequires;

    /**
     * @var callable
     */
    private $buildCall;

    /**
     * @var array
     */
    private $readVariables;

    public function getDefaultName(): string
    {
        return '';
    }

    protected function init(BuildWizardEvent $event): void
    {
        parent::init($event);
        $this->registerCall($event, __METHOD__);
    }

    protected function build(BuildWizardEvent $event): void
    {
        $this->registerCall($event, __METHOD__);
        if (\is_callable($this->buildCall)) {
            \call_user_func($this->buildCall, $event);
        }
    }

    protected function cleanUp(BuildWizardEvent $event): void
    {
        parent::cleanUp($event);
        $this->registerCall($event, __METHOD__);
    }

    private function registerCall(BuildWizardEvent $event, string $method): void
    {
        $parameters = $event->getParameters();
        $parameters[$method] = true;
        $event->setParameters($parameters);
        $this->event = $event;
    }

    public function getBuildWizardEvent(): BuildWizardEvent
    {
        return $this->event;
    }

    /**
     * @param bool|\Exception $checkRequires
     *
     * @return $this
     */
    public function setCheckRequires($checkRequires): self
    {
        $this->checkRequires = $checkRequires;

        return $this;
    }

    /**
     * @param bool $isHidden
     *
     * @return $this
     */
    public function setIsHidden(bool $isHidden): self
    {
        $this->isHidden = $isHidden;

        return $this;
    }

    /**
     * @param string|null $builtCheckFile
     *
     * @return $this
     */
    public function setBuiltCheckFile(?string $builtCheckFile): self
    {
        $this->builtCheckFile = $builtCheckFile;

        return $this;
    }

    protected function getBuiltCheckFile(): ?string
    {
        return $this->builtCheckFile;
    }

    /**
     * @param callable $buildCall
     *
     * @return $this
     */
    public function setBuildCall(callable $buildCall): self
    {
        $this->buildCall = $buildCall;

        return $this;
    }

    /**
     * @param array $readVariables
     *
     * @return $this
     */
    public function setReadVariables(array $readVariables): self
    {
        $this->readVariables = $readVariables;

        return $this;
    }

    public function isHidden(): bool
    {
        return null === $this->isHidden
            ? parent::isHidden()
            : $this->isHidden;
    }

    public function checkRequires($targetProjectDirectory): bool
    {
        if ($this->checkRequires instanceof \Exception) {
            throw $this->checkRequires;
        }

        return null === $this->checkRequires
            ? parent::checkRequires($targetProjectDirectory)
            : $this->checkRequires;
    }

    protected function readSkeletonVars(BuildWizardEvent $event): array
    {
        return null === $this->readVariables
            ? parent::readSkeletonVars($event)
            : $this->readVariables;
    }
}
