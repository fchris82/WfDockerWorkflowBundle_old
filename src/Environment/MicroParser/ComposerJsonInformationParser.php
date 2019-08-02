<?php declare(strict_types=1);
/**
 * Created by IntelliJ IDEA.
 * User: chris
 * Date: 2018.11.30.
 * Time: 12:37
 */

namespace Wf\DockerWorkflowBundle\Environment\MicroParser;

use Symfony\Component\Filesystem\Exception\FileNotFoundException;
use Symfony\Component\Filesystem\Filesystem;
use Wf\DockerWorkflowBundle\Exception\InvalidComposerVersionNumber;
use Wf\DockerWorkflowBundle\Exception\ValueIsMissingException;

class ComposerJsonInformationParser implements MicroParserInterface
{
    /**
     * @var Filesystem
     */
    protected $fileSystem;

    /**
     * @var array
     */
    protected $composerJsonConfig = [];

    /**
     * ComposerInstalledVersionParser constructor.
     *
     * @param Filesystem $fileSystem
     */
    public function __construct(Filesystem $fileSystem)
    {
        $this->fileSystem = $fileSystem;
    }

    public function get(string $workingDirectory, string $infoPath, $default = false)
    {
        $keys = explode('.', $infoPath);
        $current = $this->getComposerJsonConfig($workingDirectory);
        foreach ($keys as $key) {
            if (!\is_array($current) || !\array_key_exists($key, $current)) {
                return $default;
            }
            $current = $current[$key];
        }

        return $current;
    }

    /**
     * @param string $workingDirectory
     *
     * @throws FileNotFoundException
     *
     * @return mixed
     */
    protected function getComposerJsonConfig(string $workingDirectory)
    {
        if (!\array_key_exists($workingDirectory, $this->composerJsonConfig)) {
            $composerJsonPath = $workingDirectory . '/composer.json';
            if (!$this->fileSystem->exists($composerJsonPath)) {
                throw new FileNotFoundException(sprintf(
                    'The composer.json doesn\'t exist in the %s directory!',
                    $workingDirectory
                ));
            }

            $this->composerJsonConfig[$workingDirectory] = json_decode(file_get_contents($composerJsonPath), true);
        }

        return $this->composerJsonConfig[$workingDirectory];
    }

    public function has(string $workingDirectory, string $infoPath): bool
    {
        $value = $this->get($workingDirectory, $infoPath, new ValueIsMissingException());

        return !$value instanceof ValueIsMissingException;
    }

    /**
     * @param $versionText
     *
     * @throws InvalidComposerVersionNumber
     *
     * @return string
     */
    public function readComposerVersion(string $versionText): string
    {
        if (preg_match('{[\d\.]*\d}', $versionText, $matches)) {
            return $matches[0];
        }

        throw new InvalidComposerVersionNumber($versionText);
    }

    /**
     * @return Filesystem
     *
     * @codeCoverageIgnore Simple getter
     */
    public function getFilesystem(): Filesystem
    {
        return $this->fileSystem;
    }
}
