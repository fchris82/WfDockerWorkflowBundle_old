<?php declare(strict_types=1);
/**
 * Created by IntelliJ IDEA.
 * User: chris
 * Date: 2018.11.30.
 * Time: 16:48
 */

namespace Wf\DockerWorkflowBundle\Environment;

use Wf\DockerWorkflowBundle\Exception\CommanderRunException;

class Commander
{
    /**
     * @var IoManager
     */
    protected $ioManager;

    /**
     * @var WfEnvironmentParser
     */
    protected $wfEnvironmentParser;

    /**
     * @var string
     */
    protected $runCommandsWorkdir;

    /**
     * It is important because of tests!
     *
     * @var bool
     */
    protected $liveEcho = true;

    /**
     * Commander constructor.
     *
     * @param IoManager           $ioManager
     * @param WfEnvironmentParser $wfEnvironmentParser
     */
    public function __construct(IoManager $ioManager, WfEnvironmentParser $wfEnvironmentParser)
    {
        $this->ioManager = $ioManager;
        $this->wfEnvironmentParser = $wfEnvironmentParser;
    }

    /**
     * @param $workdir
     */
    public function cd(string $workdir): void
    {
        $this->runCommandsWorkdir = $workdir;
    }

    /**
     * @return string
     */
    protected function getCmdWorkDir(): string
    {
        return $this->runCommandsWorkdir ?: $_SERVER['PWD'];
    }

    /**
     * @param string      $cmd
     * @param string|null $workdir
     *
     * @throws CommanderRunException
     *
     * @return string
     */
    public function run(string $cmd, string $workdir = null): string
    {
        $workdir = $workdir ?: $this->getCmdWorkDir();
        $cmd = sprintf('cd %s && %s', $workdir, $cmd);
        $replace = [
            '&&' => '<question>&&</question>',
            '|' => '<question>|</question>',
        ];
        $printedCmd = str_replace(
            array_keys($replace),
            array_values($replace),
            $cmd
        );

        $this->ioManager->writeln(sprintf('[exec] <comment>%s</comment>', $printedCmd));
        $result = $this->liveExecuteCommand($cmd);
        $return = $result['exit_status'];
        $output = $result['output'];

        if (0 === $return) {
            $this->ioManager->writeln(sprintf('[<info>OK</info>] %s', $printedCmd));

            return $output;
        }

        $this->ioManager->writeln(sprintf('[<error>ERROR</error> (%d)] %s', $return, $printedCmd));
        throw new CommanderRunException($cmd, $output, '', $return);
    }

    /**
     * @param string      $cmd
     * @param string      $image
     * @param string      $extraParameters
     * @param string|null $workdir
     *
     * @throws CommanderRunException
     *
     * @return string
     */
    public function runCmdInContainer(string $cmd, string $image, string $extraParameters = '', string $workdir = null): string
    {
        $workdir = $workdir ?: $this->getCmdWorkDir();
        if ($this->wfEnvironmentParser->wfIsInitialized($workdir)) {
            $containerCmd = sprintf(
                'wf %s',
                $cmd
            );
        } else {
            $environments = [
                'LOCAL_USER_ID'         => '${LOCAL_USER_ID}',
                'LOCAL_USER_NAME'       => '${LOCAL_USER_NAME}',
                'LOCAL_USER_HOME'       => '${LOCAL_USER_HOME}',
                'WF_HOST_TIMEZONE'      => '${WF_HOST_TIMEZONE}',
                'WF_HOST_LOCALE'        => '${WF_HOST_LOCALE}',
                'WF_DOCKER_HOST_CHAIN'  => '"${WF_DOCKER_HOST_CHAIN}$(hostname) "',
                'COMPOSER_HOME'         => '${COMPOSER_HOME}',
                'COMPOSER_MEMORY_LIMIT' => '-1',
                'USER_GROUP'            => '${USER_GROUP}',
                'APP_ENV'               => 'dev',
                'XDEBUG_ENABLED'        => '0',
                'WF_DEBUG'              => '0',
                'CI'                    => '0',
                'DOCKER_RUN'            => '1',
                'WF_TTY'                => $this->isTtyEnvironment() ? '1' : '0',
            ];
            $envParameters = [];
            foreach ($environments as $name => $value) {
                $envParameters[] = sprintf('-e %s=%s', $name, $value);
            }

            // Example: `docker run -it -w $(pwd) -v $(pwd):$(pwd) -e TTY=1 -e WF_DEBUG=0 /bin/bash -c "ls -al && php -i"
            $containerCmd = sprintf(
                'docker run %1$s -u ${LOCAL_USER_ID}:${USER_GROUP} -w %2$s -v ${COMPOSER_HOME}:${COMPOSER_HOME} -v %2$s:%2$s %3$s %4$s %5$s %6$s',
                $this->isTtyEnvironment() ? '-it' : '', // 1
                $workdir,                                    // 2
                implode(' ', $envParameters),               // 3
                $extraParameters,                            // 4
                $image,                                      // 5
                $cmd                                         // 6
            );
        }

        return $this->run(
            $containerCmd,
            $workdir
        );
    }

    /**
     * @return bool
     *
     * @codeCoverageIgnore Simple getter
     */
    public function isLiveEcho(): bool
    {
        return $this->liveEcho;
    }

    /**
     * @param bool $liveEcho
     *
     * @return $this
     */
    public function setLiveEcho(bool $liveEcho): self
    {
        $this->liveEcho = $liveEcho;

        return $this;
    }

    /**
     * @param string $cmd
     *
     * @return array
     */
    protected function liveExecuteCommand(string $cmd): array
    {
        if ($this->liveEcho) {
            // @codeCoverageIgnoreStart
            while (@ob_end_flush()); // end all output buffers if any
            // @codeCoverageIgnoreEnd
        }

        $proc = popen("$cmd 2>&1 ; echo Exit status : $?", 'r');

        $live_output     = '';
        $complete_output = '';

        while (!feof($proc)) {
            $live_output     = fread($proc, 4096);
            $complete_output = $complete_output . $live_output;

            if ($this->liveEcho) {
                // @codeCoverageIgnoreStart
                echo "$live_output";
                @flush();
                // @codeCoverageIgnoreEnd
            }
        }

        pclose($proc);

        // get exit status
        preg_match('/[0-9]+$/', $complete_output, $matches);

        // return exit status and intended output
        return [
            'exit_status'  => (int) ($matches[0]),
            'output'       => rtrim(str_replace('Exit status : ' . $matches[0], '', $complete_output)),
        ];
    }

    protected function isTtyEnvironment(): bool
    {
        return stream_isatty(STDERR);
    }
}
