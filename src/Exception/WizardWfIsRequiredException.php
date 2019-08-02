<?php declare(strict_types=1);
/**
 * Created by IntelliJ IDEA.
 * User: chris
 * Date: 2018.11.12.
 * Time: 15:30
 */

namespace Wf\DockerWorkflowBundle\Exception;

use Wf\DockerWorkflowBundle\Wizards\BaseWizard;

class WizardWfIsRequiredException extends WizardSomethingIsRequiredException
{
    /**
     * @var BaseWizard
     */
    protected $wizard;

    /**
     * @var string
     */
    protected $targetProjectPath;

    public function __construct(BaseWizard $wizard, string $targetProjectPath, string $message = '', int $code = 0, \Throwable $previous = null)
    {
        $this->wizard = $wizard;
        $this->targetProjectPath = $targetProjectPath;
        if (!$message) {
            $message = sprintf('The `%s` wizard needs initialized and configured WF! (Target path: `%s`)', $wizard->getDefaultName(), $targetProjectPath);
        }

        parent::__construct($message, $code, $previous);
    }

    /**
     * @return BaseWizard
     */
    public function getWizard(): BaseWizard
    {
        return $this->wizard;
    }

    /**
     * @return string
     */
    public function getTargetProjectPath(): string
    {
        return $this->targetProjectPath;
    }
}
