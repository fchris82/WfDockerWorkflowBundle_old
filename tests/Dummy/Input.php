<?php declare(strict_types=1);
/**
 * Created by IntelliJ IDEA.
 * User: chris
 * Date: 2017.08.13.
 * Time: 21:37
 */

namespace Wf\DockerWorkflowBundle\Tests\Dummy;

use Symfony\Component\Console\Input\Input as BaseInput;

class Input extends BaseInput
{
    /**
     * Processes command line arguments.
     */
    protected function parse()
    {
        // TODO: Implement parse() method.
    }

    /**
     * Returns the first argument from the raw parameters (not parsed).
     *
     * @return string The value of the first argument or null otherwise
     */
    public function getFirstArgument()
    {
        // TODO: Implement getFirstArgument() method.
    }

    /**
     * Returns true if the raw parameters (not parsed) contain a value.
     *
     * This method is to be used to introspect the input parameters
     * before they have been validated. It must be used carefully.
     *
     * @param string|array $values     The values to look for in the raw parameters (can be an array)
     * @param bool         $onlyParams Only check real parameters, skip those following an end of options (--) signal
     *
     * @return bool true if the value is contained in the raw parameters
     */
    public function hasParameterOption($values, $onlyParams = false)
    {
        // TODO: Implement hasParameterOption() method.
    }

    /**
     * Returns the value of a raw option (not parsed).
     *
     * This method is to be used to introspect the input parameters
     * before they have been validated. It must be used carefully.
     *
     * @param string|array $values     The value(s) to look for in the raw parameters (can be an array)
     * @param mixed        $default    The default value to return if no result is found
     * @param bool         $onlyParams Only check real parameters, skip those following an end of options (--) signal
     *
     * @return mixed The option value
     */
    public function getParameterOption($values, $default = false, $onlyParams = false)
    {
        // TODO: Implement getParameterOption() method.
    }
}
