<?php declare(strict_types=1);
/**
 * Created by IntelliJ IDEA.
 * User: chris
 * Date: 2018.11.30.
 * Time: 17:03
 */

namespace Wf\DockerWorkflowBundle\Exception;

use Throwable;

class CommanderRunException extends \Exception
{
    /**
     * @var string
     */
    protected $command;

    /**
     * @var string
     */
    protected $output;

    public function __construct(string $command, string $output, string $message = '', int $code = 0, Throwable $previous = null)
    {
        $this->command = $command;
        $this->output = $output;
        parent::__construct($message, $code, $previous);
    }

    /**
     * @return string
     *
     * @codeCoverageIgnore Simple getter
     */
    public function getCommand(): string
    {
        return $this->command;
    }

    /**
     * @return string
     *
     * @codeCoverageIgnore Simple getter
     */
    public function getOutput(): string
    {
        return $this->output;
    }
}
