<?php declare(strict_types=1);
/**
 * Created by IntelliJ IDEA.
 * User: chris
 * Date: 2018.12.21.
 * Time: 17:39
 */

namespace Wf\DockerWorkflowBundle\Skeleton;

use Symfony\Component\Finder\SplFileInfo;

/**
 * Class SkeletonTwigFileInfo
 *
 * We need the twig namespace of file and path together!
 */
class SkeletonTwigFileInfo extends SplFileInfo
{
    /**
     * @var string
     */
    protected $twigNamespace;

    public function __construct(string $file, string $relativePath, string $relativePathname, string $twigNamespace)
    {
        $this->twigNamespace = $twigNamespace;
        parent::__construct($file, $relativePath, $relativePathname);
    }

    public static function create(SplFileInfo $fileInfo, string $twigNamespace): self
    {
        return new static($fileInfo->getPathname(), $fileInfo->getRelativePath(), $fileInfo->getRelativePathname(), $twigNamespace);
    }

    /**
     * @return string
     *
     * @codeCoverageIgnore Simple getter
     */
    public function getTwigNamespace(): string
    {
        return $this->twigNamespace;
    }

    protected function getDirectory(): string
    {
        return SkeletonHelper::SKELETONS_DIR;
    }

    public function getTwigPath(): string
    {
        return sprintf(
            '@%s/%s/%s',
            $this->twigNamespace,
            $this->getDirectory(),
            $this->getRelativePathname()
        );
    }
}
