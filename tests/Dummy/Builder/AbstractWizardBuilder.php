<?php declare(strict_types=1);
/**
 * Created by IntelliJ IDEA.
 * User: chris
 * Date: 2019.01.15.
 * Time: 14:26
 */

namespace Wf\DockerWorkflowBundle\Tests\Dummy\Builder;

use Mockery as m;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Event\ConsoleCommandEvent;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Tests\Fixtures\DummyOutput;
use Symfony\Component\EventDispatcher\EventDispatcher;
use Twig\Environment;
use Twig\Loader\FilesystemLoader;
use Wf\DockerWorkflowBundle\Environment\Commander;
use Wf\DockerWorkflowBundle\Environment\IoManager;
use Wf\DockerWorkflowBundle\Environment\WfEnvironmentParser;
use Wf\DockerWorkflowBundle\Test\Dummy\Filesystem;
use Wf\DockerWorkflowBundle\Tests\Dummy\Input;
use Wf\DockerWorkflowBundle\Tests\Dummy\QuestionHelper;
use Wf\DockerWorkflowBundle\Twig\Extension\TextExtension;

/**
 * Class AbstractWizardBuilder
 *
 * It can help to create wizards
 */
abstract class AbstractWizardBuilder
{
    /**
     * @var IoManager
     */
    protected $ioManager;

    /**
     * @var Commander
     */
    protected $commander;

    /**
     * @var EventDispatcher
     */
    protected $eventDispatcher;

    /**
     * @var Environment
     */
    protected $twig;

    /**
     * @var Filesystem
     */
    protected $fileSystem;

    /**
     * @var InputInterface
     */
    protected $input;

    /**
     * @var OutputInterface
     */
    protected $output;

    /**
     * @var WfEnvironmentParser
     */
    protected $wfEnvironmentParser;

    /**
     * @var QuestionHelper
     */
    protected $questionHelper;

    /**
     * WizardBuilder constructor.
     */
    public function __construct()
    {
        $this->initIo();
        $this->fileSystem = new Filesystem('test');
        $this->questionHelper = new QuestionHelper();
        $this->wfEnvironmentParser = m::mock(WfEnvironmentParser::class);
        $this->initIoManager();
        $this->initCommander();
        $this->eventDispatcher = new EventDispatcher();
        $this->initTwig();
    }

    abstract public function build();

    public function setQuestionResponses(array $responses)
    {
        /** @var QuestionHelper $questionHelper */
        $this->questionHelper->setResponses($responses);
    }

    // ----------------------------------------------------- INIT ------------------------------------------------------
    protected function initIo()
    {
        $this->input = new Input();
        $this->output = new DummyOutput();
    }

    protected function initIoManager()
    {
        $this->ioManager = new IoManager();
        $command = m::mock(Command::class);
        $command
            ->shouldReceive('getHelper')
            ->with('question')
            ->zeroOrMoreTimes()
            ->andReturn($this->questionHelper);
        $event = new ConsoleCommandEvent($command, $this->input, $this->output);
        $this->ioManager->init($event);

        // Symfony IO
        $sfIo = $this->ioManager->getIo();
        $ioClass = new \ReflectionClass($sfIo);
        $qhProperty = $ioClass->getProperty('questionHelper');
        $qhProperty->setValue($sfIo, $this->questionHelper);
    }

    protected function initCommander()
    {
        $this->commander = new Commander($this->ioManager, $this->wfEnvironmentParser);
    }

    protected function initTwig()
    {
        $twigLoader = new FilesystemLoader();
        $this->twig = new Environment($twigLoader);
        $this->twig->addExtension(new TextExtension());
    }

    // ---------------------------------------------------- GET-SET ----------------------------------------------------

    /**
     * @return IoManager
     */
    public function getIoManager(): IoManager
    {
        return $this->ioManager;
    }

    /**
     * @param IoManager $ioManager
     *
     * @return $this
     */
    public function setIoManager(IoManager $ioManager)
    {
        $this->ioManager = $ioManager;

        return $this;
    }

    /**
     * @return Commander
     */
    public function getCommander(): Commander
    {
        return $this->commander;
    }

    /**
     * @param Commander $commander
     *
     * @return $this
     */
    public function setCommander(Commander $commander)
    {
        $this->commander = $commander;

        return $this;
    }

    /**
     * @return EventDispatcher
     */
    public function getEventDispatcher(): EventDispatcher
    {
        return $this->eventDispatcher;
    }

    /**
     * @param EventDispatcher $eventDispatcher
     *
     * @return $this
     */
    public function setEventDispatcher(EventDispatcher $eventDispatcher)
    {
        $this->eventDispatcher = $eventDispatcher;

        return $this;
    }

    /**
     * @return Environment
     */
    public function getTwig(): Environment
    {
        return $this->twig;
    }

    /**
     * @param Environment $twig
     *
     * @return $this
     */
    public function setTwig(Environment $twig)
    {
        $this->twig = $twig;

        return $this;
    }

    /**
     * @return Filesystem
     */
    public function getFileSystem(): Filesystem
    {
        return $this->fileSystem;
    }

    /**
     * @param Filesystem $fileSystem
     *
     * @return $this
     */
    public function setFileSystem(Filesystem $fileSystem)
    {
        $this->fileSystem = $fileSystem;

        return $this;
    }

    /**
     * @return InputInterface
     */
    public function getInput(): InputInterface
    {
        return $this->input;
    }

    /**
     * @param InputInterface $input
     *
     * @return $this
     */
    public function setInput(InputInterface $input)
    {
        $this->input = $input;

        return $this;
    }

    /**
     * @return OutputInterface
     */
    public function getOutput(): OutputInterface
    {
        return $this->output;
    }

    /**
     * @param OutputInterface $output
     *
     * @return $this
     */
    public function setOutput(OutputInterface $output)
    {
        $this->output = $output;

        return $this;
    }

    /**
     * @return WfEnvironmentParser
     */
    public function getWfEnvironmentParser(): WfEnvironmentParser
    {
        return $this->wfEnvironmentParser;
    }

    /**
     * @param WfEnvironmentParser $wfEnvironmentParser
     *
     * @return $this
     */
    public function setWfEnvironmentParser(WfEnvironmentParser $wfEnvironmentParser)
    {
        $this->wfEnvironmentParser = $wfEnvironmentParser;

        return $this;
    }
}
