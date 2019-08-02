<?php declare(strict_types=1);
/**
 * Created by IntelliJ IDEA.
 * User: chris
 * Date: 2017.08.13.
 * Time: 21:34
 */

namespace Wf\DockerWorkflowBundle\Tests\Dummy;

use Symfony\Component\Console\Helper\QuestionHelper as BaseQuestionHelper;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\Question;

class QuestionHelper extends BaseQuestionHelper
{
    /**
     * @var array
     */
    protected $responses;

    /**
     * QuestionHelper constructor.
     *
     * @param array $responses
     */
    public function __construct(array $responses = [])
    {
        $this->responses = $responses;
    }

    /**
     * @return array
     */
    public function getResponses(): array
    {
        return $this->responses;
    }

    /**
     * @param array $responses
     *
     * @return $this
     */
    public function setResponses(array $responses)
    {
        $this->responses = $responses;

        return $this;
    }

    public function ask(InputInterface $input, OutputInterface $output, Question $question)
    {
        if (!\array_key_exists(trim($question->getQuestion()), $this->responses)) {
            throw new \Exception(sprintf('Invalid configuration, the `%s` question\'s response doesn\'t exist!', $question->getQuestion()));
        }

        return $this->responses[trim($question->getQuestion())];
    }
}
