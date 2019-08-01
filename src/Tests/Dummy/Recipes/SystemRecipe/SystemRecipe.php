<?php declare(strict_types=1);
/**
 * Created by IntelliJ IDEA.
 * User: chris
 * Date: 2019.01.22.
 * Time: 13:47
 */

namespace Webtown\WorkflowBundle\Tests\Dummy\Recipes\SystemRecipe;

use Symfony\Component\Config\Definition\Builder\NodeDefinition;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;
use Twig\Environment;

class SystemRecipe extends \Webtown\WorkflowBundle\Recipes\SystemRecipe
{
    /**
     * @var string
     */
    protected $name;

    /**
     * @var NodeDefinition
     */
    protected $configuration;

    /**
     * SystemRecipe constructor.
     *
     * @param string                   $name
     * @param NodeDefinition           $configuration
     * @param Environment              $twig
     * @param EventDispatcherInterface $eventDispatcher
     */
    public function __construct($name, $configuration, Environment $twig, EventDispatcherInterface $eventDispatcher)
    {
        $this->name = $name;
        $this->configuration = $configuration;
        parent::__construct($twig, $eventDispatcher);
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getConfig(): NodeDefinition
    {
        return $this->configuration;
    }
}
