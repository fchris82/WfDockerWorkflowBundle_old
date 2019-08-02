<?php declare(strict_types=1);
/**
 * Created by IntelliJ IDEA.
 * User: chris
 * Date: 2018.08.06.
 * Time: 12:26
 */

namespace Wf\DockerWorkflowBundle\Exception;

use Throwable;

class InvalidComposerVersionNumber extends \InvalidArgumentException
{
    /**
     * @var string
     */
    protected $version;

    public function __construct(string $version = null, string $message = '', int $code = 0, Throwable $previous = null)
    {
        $this->version = $version;

        parent::__construct($message, $code, $previous);
    }

    /**
     * @return string
     *
     * @codeCoverageIgnore Simple getter
     */
    public function getVersion(): string
    {
        return $this->version;
    }
}
