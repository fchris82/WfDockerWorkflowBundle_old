<?php declare(strict_types=1);
/**
 * Created by IntelliJ IDEA.
 * User: chris
 * Date: 2018.03.14.
 * Time: 22:26
 */

namespace Wf\DockerWorkflowBundle\Configuration;

use Symfony\Component\Config\Definition\Exception\InvalidConfigurationException;
use Wf\DockerWorkflowBundle\Exception\MissingRecipeException;
use Wf\DockerWorkflowBundle\Recipes\BaseRecipe;

class RecipeManager
{
    /**
     * @var BaseRecipe[]
     */
    protected $recipes = [];

    public function addRecipe(BaseRecipe $recipe): void
    {
        if (\array_key_exists($recipe->getName(), $this->recipes)) {
            throw new InvalidConfigurationException(sprintf(
                'The `%s` recipe has been already existed! [`%s` vs `%s`]',
                $recipe->getName(),
                \get_class($this->recipes[$recipe->getName()]),
                \get_class($recipe)
            ));
        }
        $this->recipes[$recipe->getName()] = $recipe;
    }

    /**
     * @return BaseRecipe[]
     */
    public function getRecipes(): array
    {
        return $this->recipes;
    }

    /**
     * @param string $recipeName
     *
     * @throws MissingRecipeException
     *
     * @return BaseRecipe
     */
    public function getRecipe(string $recipeName): BaseRecipe
    {
        $recipes = $this->getRecipes();
        if (!\array_key_exists($recipeName, $recipes)) {
            throw new MissingRecipeException(sprintf('The `%s` recipe is missing!', $recipeName));
        }

        return $recipes[$recipeName];
    }
}
