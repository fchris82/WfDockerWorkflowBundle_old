<?php declare(strict_types=1);
/**
 * Created by IntelliJ IDEA.
 * User: chris
 * Date: 2018.12.28.
 * Time: 13:52
 */

namespace Wf\DockerWorkflowBundle\Tests\Recipes;

use Mockery as m;
use PHPUnit\Framework\TestCase;
use Symfony\Component\EventDispatcher\EventDispatcher;
use Twig\Environment;
use Wf\DockerWorkflowBundle\Tests\Dummy\Recipes\Hidden\HiddenRecipe;

class HiddenRecipeTest extends TestCase
{
    public function testGetConfig()
    {
        $twigEnv = m::mock(Environment::class);
        $eventDispatcher = new EventDispatcher();
        $hiddenRecipe = new HiddenRecipe($twigEnv, $eventDispatcher);

        $this->expectException(\Exception::class);

        $hiddenRecipe->getConfig();
    }
}
