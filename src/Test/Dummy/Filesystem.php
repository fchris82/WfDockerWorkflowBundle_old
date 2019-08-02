<?php declare(strict_types=1);
/**
 * Created by IntelliJ IDEA.
 * User: chris
 * Date: 2017.08.13.
 * Time: 21:18
 */

namespace Wf\DockerWorkflowBundle\Test\Dummy;

use Symfony\Component\Filesystem\Exception\FileNotFoundException;
use Symfony\Component\Filesystem\Filesystem as BaseFilesystem;
use Symfony\Component\Finder\Finder;

class Filesystem extends BaseFilesystem
{
    const DIRECTORY_ID = '-- DIRECTORY --';

    /**
     * @var string|null
     */
    protected $initDirectory;

    /**
     * @var string|null
     */
    protected $alias;

    /**
     * @var array
     */
    protected $contents = [];

    public function __construct($initDirectory, $alias = null)
    {
        $this->initDirectory = $initDirectory;
        $this->alias = $alias;

        $finder = new Finder();
        $finder->files()->ignoreDotFiles(false)->in($initDirectory);
        foreach ($finder as $fileInfo) {
            $path = $this->aliasMask($fileInfo->getPathname());
            $content = file_get_contents($fileInfo->getPathname());
            $this->contents[$path] = $content;
        }
    }

    protected function aliasMask(string $path): string
    {
        if (null !== $this->alias) {
            return str_replace($this->initDirectory, $this->alias, $path);
        }

        return $path;
    }

    public function getContents(): array
    {
        ksort($this->contents);

        return $this->contents;
    }

    /**
     * @param iterable|string $files
     *
     * @return bool
     */
    public function exists($files): bool
    {
        $files = $this->pathsToArray($files);
        $hits = [];
        foreach ($files as $file) {
            $file = $this->aliasMask((string) $file);
            foreach ($this->contents as $path => $content) {
                if ($path == $file || 0 === strpos($path, $file . \DIRECTORY_SEPARATOR)) {
                    $hits[] = $file;
                    continue 2;
                }
            }
        }

        return \count($files) == \count($hits);
    }

    public function dumpFile($filename, $content)
    {
        $filename = $this->aliasMask($filename);
        $this->contents[$filename] = $content;
    }

    public function appendToFile($filename, $content)
    {
        $filename = $this->aliasMask($filename);
        $base = isset($this->contents[$filename]) ? $this->contents[$filename] : '';
        $this->contents[$filename] = $base . $content;
    }

    /**
     * !!! Limitation !!!
     *
     * Can't exists same filename and dirname at the same time!
     *
     * @param iterable|string $dirs
     * @param int             $mode
     */
    public function mkdir($dirs, $mode = 0777)
    {
        $dirs = $this->pathsToArray($dirs);
        foreach ($dirs as $dir) {
            $targetDir = $this->aliasMask((string) $dir);
            if (!$this->exists($targetDir)) {
                $this->dumpFile($targetDir, static::DIRECTORY_ID);
            }
        }

        return;
    }

    public function touch($files, $time = null, $atime = null)
    {
        $files = $this->pathsToArray($files);
        foreach ($files as $file) {
            $targetFile = $this->aliasMask((string) $file);
            if (!$this->exists($targetFile)) {
                $this->dumpFile($targetFile, '');
            }
        }
    }

    public function copy($origin, $target, $overwriteNewerFiles = false)
    {
        $origin = $this->aliasMask($origin);
        $target = $this->aliasMask($target);

        $copied = 0;
        foreach ($this->contents as $path => $content) {
            if ($origin == $path || 0 === strpos($path, $origin . \DIRECTORY_SEPARATOR)) {
                ++$copied;
                $newPath = str_replace($origin, $target, $path);
                if (!$this->exists($newPath) || $overwriteNewerFiles) {
                    $this->contents[$newPath] = $content;
                }
            }
        }

        if (0 == $copied) {
            throw new FileNotFoundException();
        }
    }

    public function rename($origin, $target, $overwrite = false)
    {
        $origin = $this->aliasMask($origin);
        $target = $this->aliasMask($target);

        $renamed = 0;
        $newContents = [];
        foreach ($this->contents as $path => $content) {
            if ($origin == $path || 0 === strpos($path, $origin . \DIRECTORY_SEPARATOR)) {
                ++$renamed;
                $newPath = str_replace($origin, $target, $path);
                if (!$this->exists($newPath) || $overwrite) {
                    $newContents[$newPath] = $content;
                }
            } else {
                if (!\array_key_exists($path, $newContents)) {
                    $newContents[$path] = $content;
                }
            }
        }

        if (0 == $renamed) {
            throw new FileNotFoundException();
        }

        $this->contents = $newContents;
    }

    public function remove($files)
    {
        $files = $this->pathsToArray($files);
        foreach ($files as $file) {
            $file = $this->aliasMask((string) $file);
            foreach ($this->contents as $path => $content) {
                if ($path == $file || 0 === strpos($path, $file . \DIRECTORY_SEPARATOR)) {
                    unset($this->contents[$path]);
                    continue 2;
                }
            }
        }
    }

    public function chmod($files, $mode, $umask = 0000, $recursive = false)
    {
        // @todo (Chris)
    }

    /**
     * @param string|array|string[]|iterable $paths
     *
     * @return array|iterable|\SplFileObject[]
     */
    protected function pathsToArray($paths): iterable
    {
        return \is_array($paths) || $paths instanceof \IteratorAggregate
            ? $paths
            : [$paths];
    }
}
