<?php declare(strict_types=1);
/**
 * Created by IntelliJ IDEA.
 * User: chris
 * Date: 2018.03.24.
 * Time: 21:56
 */

namespace Wf\DockerWorkflowBundle\Skeleton\FileType;

use Symfony\Component\Finder\SplFileInfo;

class SkeletonFile
{
    const HANDLE_EXISTING_FULL_OVERWRITE = 0;
    const HANDLE_EXISTING_APPEND = 1;

    /**
     * @var SplFileInfo
     */
    protected $baseFileInfo;

    /**
     * @var string|null
     */
    protected $relativePath;

    /**
     * @var string|null
     */
    protected $fileName;

    /**
     * @var string|null
     */
    protected $fullTargetPathname;

    /**
     * @var string
     */
    protected $contents;

    /**
     * @var int
     */
    protected $handleExisting;

    /**
     * SkeletonFile constructor.
     *
     * @param SplFileInfo $fileInfo
     */
    public function __construct(SplFileInfo $fileInfo)
    {
        $this->baseFileInfo = $fileInfo;
        $this->handleExisting = static::HANDLE_EXISTING_FULL_OVERWRITE;
    }

    /**
     * @return SplFileInfo
     */
    public function getBaseFileInfo(): SplFileInfo
    {
        return $this->baseFileInfo;
    }

    /**
     * @param SplFileInfo $baseFileInfo
     *
     * @return $this
     *
     * @codeCoverageIgnore Simple setter
     */
    public function setBaseFileInfo($baseFileInfo): self
    {
        $this->baseFileInfo = $baseFileInfo;

        return $this;
    }

    /**
     * @return string|null
     */
    public function getRelativePath(): ?string
    {
        return $this->relativePath ?: $this->getBaseFileInfo()->getRelativePath();
    }

    /**
     * @param string|null $relativePath
     *
     * @return $this
     *
     * @deprecated Use move() instead
     */
    public function setRelativePath(?string $relativePath): self
    {
        $this->relativePath = rtrim($relativePath, \DIRECTORY_SEPARATOR);

        return $this;
    }

    /**
     * @return string|null
     */
    public function getFileName(): ?string
    {
        return $this->fileName ?: $this->getBaseFileInfo()->getFilename();
    }

    /**
     * @param string|null $newFileName
     *
     * @return $this
     *
     * @deprecated Use the rename() instead
     */
    public function setFileName(?string $newFileName): self
    {
        $relativeDirectory = rtrim($this->getRelativePath(), \DIRECTORY_SEPARATOR);
        $this->setRelativePathname($relativeDirectory . \DIRECTORY_SEPARATOR . $newFileName);

        $this->fileName = $newFileName;

        return $this;
    }

    /**
     * @return string|null
     */
    public function getRelativePathname(): ?string
    {
        return $this->relativePath
            ? $this->getRelativePath() . \DIRECTORY_SEPARATOR . $this->getFileName()
            : $this->getBaseFileInfo()->getRelativePathname();
    }

    /**
     * @param string|null $relativePathname
     *
     * @return $this
     *
     * @deprecated Use the move() instead
     */
    public function setRelativePathname(?string $relativePathname): self
    {
        $this->relativePathname = $relativePathname;

        return $this;
    }

    /**
     * @return string|null
     */
    public function getFullTargetPathname(): ?string
    {
        return $this->fullTargetPathname ?: $this->getRelativePathname();
    }

    /**
     * @param string|null $fullTargetPathname
     *
     * @return $this
     *
     * @deprecated Use the move() instead
     */
    public function setFullTargetPathname(?string $fullTargetPathname): self
    {
        $this->fullTargetPathname = $fullTargetPathname;

        return $this;
    }

    /**
     * @return string
     */
    public function getContents(): string
    {
        return null === $this->contents ? $this->baseFileInfo->getContents() : $this->contents;
    }

    /**
     * @param string $contents
     *
     * @return $this
     */
    public function setContents(string $contents): self
    {
        $this->contents = $contents;

        return $this;
    }

    /**
     * @return int
     */
    public function getHandleExisting(): int
    {
        return $this->handleExisting;
    }

    /**
     * @param int $handleExisting
     *
     * @return $this
     */
    public function setHandleExisting(int $handleExisting): self
    {
        $this->handleExisting = $handleExisting;

        return $this;
    }

    public function move(string $directory): self
    {
        $directory = rtrim($directory, \DIRECTORY_SEPARATOR);
        $this->setFullTargetPathname($directory . \DIRECTORY_SEPARATOR . $this->getRelativePathname());

        return $this;
    }

    public function rename(string $newFilename): void
    {
        $this->setFileName($newFilename);
        if ($this->fullTargetPathname) {
            $pathItems = explode(\DIRECTORY_SEPARATOR, $this->fullTargetPathname);
            $currentFilename = array_pop($pathItems);
            $pathItems[] = $newFilename;
            $this->setFullTargetPathname(implode(\DIRECTORY_SEPARATOR, $pathItems));
        }
    }
}
