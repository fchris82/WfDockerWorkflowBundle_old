<?php declare(strict_types=1);
/**
 * Created by IntelliJ IDEA.
 * User: chris
 * Date: 2018.12.28.
 * Time: 12:52
 */

namespace Wf\DockerWorkflowBundle\Tests\Dummy\Recipes\Simple;

use Wf\DockerWorkflowBundle\Recipes\BaseRecipe;

class SimpleRecipe extends BaseRecipe
{
    /**
     * Parent class names
     *
     * @var array|string[]
     */
    protected static $skeletonParents = [];

    public function getName(): string
    {
        return 'simple';
    }

    /**
     * @return array|string[]
     */
    public static function getSkeletonParents(): array
    {
        return array_merge(parent::getSkeletonParents(), self::$skeletonParents);
    }

    /**
     * @param array|string[] $skeletonParents
     */
    public static function setSkeletonParents($skeletonParents): void
    {
        self::$skeletonParents = $skeletonParents;
    }

    public function makefileFormat(string $pattern, array $items): string
    {
        return $this->makefileMultilineFormatter($pattern, $items);
    }
}
