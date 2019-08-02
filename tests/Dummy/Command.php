<?php declare(strict_types=1);
/**
 * Created by IntelliJ IDEA.
 * User: chris
 * Date: 2017.08.13.
 * Time: 21:41
 */

namespace Wf\DockerWorkflowBundle\Tests\Dummy;

use Symfony\Component\Console\Command\Command as BaseCommand;

class Command extends BaseCommand
{
    private $questionResponses = [];

    public function setQuetionResponses(array $responses)
    {
        $this->questionResponses = $responses;
    }

    public function getHelper($name)
    {
        $helpers = [
            'question' => new QuestionHelper($this->questionResponses),
        ];

        if (!\array_key_exists($name, $helpers)) {
            throw new \Exception(sprintf('Invalid helper calling: `%s`.', $name));
        }

        return $helpers[$name];
    }
}
