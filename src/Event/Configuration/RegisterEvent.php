<?php declare(strict_types=1);
/**
 * Created by IntelliJ IDEA.
 * User: chris
 * Date: 2019.01.18.
 * Time: 19:20
 */

namespace Wf\DockerWorkflowBundle\Event\Configuration;

use Symfony\Contracts\EventDispatcher\Event;
use Wf\DockerWorkflowBundle\Recipes\BaseRecipe;

class RegisterEvent extends Event
{
    /**
     * Project path. You can't modify this in this event!
     *
     * @var string
     */
    protected $projectPath;

    /**
     * Project config. You can't modify this in this event! If you want to change it, use the
     * `ConfigurationEvents::BUILD_INIT` eg.
     *
     * @var array
     */
    protected $config;

    /**
     * @var array|BaseRecipe[]
     */
    protected $recipes = [];

    /**
     * RegisterEvent constructor.
     *
     * @param string $projectPath
     * @param array  $config
     */
    public function __construct(string $projectPath, array $config)
    {
        $this->projectPath = $projectPath;
        $this->config = $config;
    }

    /**
     * @return string
     *
     * @codeCoverageIgnore Simple getter
     */
    public function getProjectPath(): string
    {
        return $this->projectPath;
    }

    /**
     * @return array
     *
     * @codeCoverageIgnore Simple getter
     */
    public function getConfig(): array
    {
        return $this->config;
    }

    /**
     * @return BaseRecipe[]|array
     *
     * @codeCoverageIgnore Simple getter
     */
    public function getRecipes(): array
    {
        return $this->recipes;
    }

    /**
     * @param BaseRecipe $recipe
     *
     * @return $this
     */
    public function addRecipe(BaseRecipe $recipe): self
    {
        $this->recipes[] = $recipe;

        return $this;
    }

    /**
     * @param BaseRecipe[]|array $recipes
     *
     * @return $this
     *
     * @codeCoverageIgnore Simple setter
     */
    public function setRecipes($recipes): self
    {
        $this->recipes = $recipes;

        return $this;
    }
}
