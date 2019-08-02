<?php declare(strict_types=1);
/**
 * Created by IntelliJ IDEA.
 * User: chris
 * Date: 2018.12.21.
 * Time: 17:20
 */

namespace Wf\DockerWorkflowBundle\Skeleton;

class SkeletonHelper
{
    /** @var string */
    const SKELETONS_DIR = 'skeletons';
    /** @var string */
    const TEMPLATES_DIR = 'template';

    public static function generateTwigNamespace(\ReflectionClass $class): string
    {
        $className = $class->getName();

        return str_replace('\\', '', $className);
    }
}
