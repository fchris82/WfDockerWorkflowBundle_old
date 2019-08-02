<?php declare(strict_types=1);
/**
 * Created by IntelliJ IDEA.
 * User: chris
 * Date: 2019.01.28.
 * Time: 11:55
 */

namespace Wf\DockerWorkflowBundle\Tests\Dummy\Environment;

use Symfony\Component\Console\Output\BufferedOutput;

class IoManager extends \Wf\DockerWorkflowBundle\Environment\IoManager
{
    public function __construct()
    {
        $this->output = new BufferedOutput();
    }

    /**
     * @var array
     */
    protected $outputLog = [];

    public function writeln($text): void
    {
        $this->outputLog[] = $text;
    }

    public function getLog(): array
    {
        return $this->outputLog;
    }

    public function getLogAsString(): string
    {
        return implode("\n", $this->outputLog);
    }
}
