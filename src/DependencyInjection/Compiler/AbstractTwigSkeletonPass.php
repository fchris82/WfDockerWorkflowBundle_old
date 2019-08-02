<?php declare(strict_types=1);
/**
 * Created by IntelliJ IDEA.
 * User: chris
 * Date: 2018.12.21.
 * Time: 16:01
 */

namespace Wf\DockerWorkflowBundle\DependencyInjection\Compiler;

use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\Definition;
use Wf\DockerWorkflowBundle\Skeleton\SkeletonHelper;

/**
 * Class AbstractTwigSkeletonPass
 *
 * Add capability to register twig namespaces and paths.
 *
 * @see SkeletonHelper::generateTwigNamespace()
 */
abstract class AbstractTwigSkeletonPass implements CompilerPassInterface
{
    const DEFAULT_TWIG_LOADER = 'twig.loader.native_filesystem';

    /**
     * Register custom twig namespaces and paths.
     * Eg, file: App\Test\TestBundle\Recipes\TestRecipe.php
     *
     *  -> namespace: AppTestTestBundleRecipesTestRecipe
     *  -> path:
     *          1. [project]/templates/bundles/[namespace] --> [project]/templates/bundles/AppTestTestBundleRecipesTestRecipe/skeletons/overridden.file.php
     *          If the previous doesn't exist:
     *          2. [php_file_dir] --> [project]/src/App/Test/TestBundle/Recipes/skeletons/original.file.php
     *
     * @param string     $twigDefaultPath
     * @param Definition $serviceDefinition
     * @param Definition $twigLoaderDefinition
     *
     * @throws \ReflectionException
     */
    protected function registerSkeletonService(string $twigDefaultPath, Definition $serviceDefinition, Definition $twigLoaderDefinition): void
    {
        $refClass = new \ReflectionClass($serviceDefinition->getClass());

        $namespace = SkeletonHelper::generateTwigNamespace($refClass);

        // Override
        $overridePath = $twigDefaultPath . \DIRECTORY_SEPARATOR . 'bundles' . \DIRECTORY_SEPARATOR . $namespace;
        if (is_dir($overridePath)) {
            $twigLoaderDefinition->addMethodCall('addPath', [$overridePath, $namespace]);
        }

        $path = \dirname($refClass->getFileName());
        $twigLoaderDefinition->addMethodCall('addPath', [$path, $namespace]);
    }
}
