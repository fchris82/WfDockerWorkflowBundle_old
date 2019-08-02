<?php declare(strict_types=1);
/**
 * Created by IntelliJ IDEA.
 * User: chris
 * Date: 2018.12.28.
 * Time: 8:44
 */

namespace Wf\DockerWorkflowBundle\Tests\Configuration;

use Mockery as m;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Config\Definition\Exception\InvalidConfigurationException;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;
use Twig\Environment;
use Wf\DockerWorkflowBundle\Configuration\RecipeManager;
use Wf\DockerWorkflowBundle\Exception\MissingRecipeException;
use Wf\DockerWorkflowBundle\Recipes\BaseRecipe;

class RecipeManagerTest extends TestCase
{
    public function tearDown(): void
    {
        m::close();
    }

    /**
     * @param array            $recipeNames
     * @param array|\Exception $result
     *
     * @dataProvider getAdds
     */
    public function testAddRecipe(array $recipeNames, $result = null)
    {
        if ($result instanceof \Exception) {
            $this->expectException(\get_class($result));
        }

        $recipeManager = new RecipeManager();
        foreach ($recipeNames as $recipeName) {
            $recipeManager->addRecipe(new TestRecipe($recipeName));
        }

        $recipes = $recipeManager->getRecipes();
        if (!$result instanceof \Exception) {
            $addedNames = [];
            foreach ($recipes as $recipe) {
                $addedNames[] = $recipe->getName();
            }
            $this->assertEquals($recipeNames, $addedNames);
        }
    }

    public function getAdds()
    {
        return [
            [[]],
            [['Test1Recipe']],
            [['Test1Recipe', 'Test2Recipe', 'Test3Recipe']],
            [['Test1Recipe', 'Test2Recipe', 'Test1Recipe'], new InvalidConfigurationException()],
        ];
    }

    /**
     * @param array           $recipeNames
     * @param string          $getName
     * @param \Exception|null $result
     *
     * @throws MissingRecipeException
     *
     * @dataProvider getGets
     */
    public function testGetRecipe(array $recipeNames, string $getName, $result = null)
    {
        if ($result instanceof \Exception) {
            $this->expectException(\get_class($result));
        }

        $recipeManager = new RecipeManager();
        foreach ($recipeNames as $recipeName) {
            $recipeManager->addRecipe(new TestRecipe($recipeName));
        }

        $recipe = $recipeManager->getRecipe($getName);
        if (!$result instanceof \Exception) {
            $this->assertEquals($recipe->getName(), $getName);
        }
    }

    public function getGets()
    {
        return [
            [[], '', new MissingRecipeException()],
            [['Test1Recipe'], 'Test1Recipe'],
            [['Test1Recipe', 'Test2Recipe', 'Test3Recipe'], 'Test3Recipe'],
            [['Test1Recipe', 'Test2Recipe', 'Test3Recipe'], 'Test4Recipe', new MissingRecipeException()],
        ];
    }
}

class TestRecipe extends BaseRecipe
{
    /**
     * @var string
     */
    protected $name;

    public function __construct($name)
    {
        $this->name = $name;
        $twigEnv = m::mock(Environment::class);
        $eventDispatcher = m::mock(EventDispatcherInterface::class);
        parent::__construct($twigEnv, $eventDispatcher);
    }

    public function getName(): string
    {
        return $this->name;
    }
}
