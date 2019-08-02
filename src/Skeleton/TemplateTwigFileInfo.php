<?php declare(strict_types=1);

namespace Wf\DockerWorkflowBundle\Skeleton;

class TemplateTwigFileInfo extends SkeletonTwigFileInfo
{
    /**
     * You can override the default template directory.
     *
     * @var string
     */
    protected $dir;

    public function __construct(
        string $file,
        string $relativePath,
        string $relativePathname,
        string $twigNamespace,
        string $dir = SkeletonHelper::TEMPLATES_DIR
    ) {
        parent::__construct($file, $relativePath, $relativePathname, $twigNamespace);
        $this->dir = $dir;
    }

    protected function getDirectory(): string
    {
        return $this->dir;
    }
}
