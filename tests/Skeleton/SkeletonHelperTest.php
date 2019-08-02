<?php declare(strict_types=1);
/**
 * Created by IntelliJ IDEA.
 * User: chris
 * Date: 2018.12.28.
 * Time: 14:08
 */

namespace Wf\DockerWorkflowBundle\Tests\Skeleton;

use PHPUnit\Framework\TestCase;
use Wf\DockerWorkflowBundle\Skeleton\SkeletonHelper;

class SkeletonHelperTest extends TestCase
{
    /**
     * @param $class
     * @param $result
     *
     * @throws \ReflectionException
     *
     * @dataProvider getNamespaces
     */
    public function testGenerateTwigNamespace($class, $result)
    {
        $reflectionClass = new \ReflectionClass($class);

        $response = SkeletonHelper::generateTwigNamespace($reflectionClass);
        $this->assertEquals($result, $response);
    }

    public function getNamespaces(): array
    {
        return [
            [\Exception::class, 'Exception'],
            [SkeletonHelper::class, 'WfDockerWorkflowBundleSkeletonSkeletonHelper'],
        ];
    }
}
