<?php declare(strict_types=1);
/**
 * Created by IntelliJ IDEA.
 * User: chris
 * Date: 2018.03.28.
 * Time: 21:33
 */

namespace Wf\DockerWorkflowBundle\Configuration;

/**
 * Class Environment
 *
 * @codeCoverageIgnore Simple getter
 */
class Environment
{
    const CONFIG_PROGRAM_REPOSITORY = 'WF_PROGRAM_REPOSITORY';
    const CONFIG_DEFAULT_LOCAL_TLD  = 'WF_DEFAULT_LOCAL_TLD';
    const CONFIG_WORKING_DIRECTORY  = 'WF_WORKING_DIRECTORY_NAME';
    const CONFIG_CONFIGURATION_FILE = 'WF_CONFIGURATION_FILE_NAME';
    const CONFIG_ENV_FILE           = 'WF_ENV_FILE_NAME';

    /**
     * @var array|null
     */
    protected $env;

    public function getConfigValue(string $name, $default = null)
    {
        $config = $this->getEnv();
        if (!\array_key_exists($name, $config)) {
            return $default;
        }

        return $config[$name];
    }

    protected function getEnv(): array
    {
        if (null === $this->env) {
            $this->env = $_ENV;
        }

        return $this->env;
    }
}
