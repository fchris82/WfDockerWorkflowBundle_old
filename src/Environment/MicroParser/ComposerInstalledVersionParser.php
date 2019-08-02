<?php declare(strict_types=1);
/**
 * Created by IntelliJ IDEA.
 * User: chris
 * Date: 2018.11.30.
 * Time: 12:37
 */

namespace Wf\DockerWorkflowBundle\Environment\MicroParser;

use Symfony\Component\Filesystem\Exception\FileNotFoundException;

class ComposerInstalledVersionParser extends ComposerJsonInformationParser
{
    protected $composerLockConfig = [];

    /**
     * Read the installed version number of a package:
     *
     *  1. Try to find in `composer.lock`
     *  2. Try to find in `composer.json` (require)
     *  3. Try to find in `composer.json` (require-dev)
     *
     * @param string $workingDirectory
     * @param string $packageName
     * @param bool   $default
     *
     * @return string
     */
    public function get(string $workingDirectory, string $packageName, $default = false)
    {
        try {
            $lockData = $this->getComposerLockConfig($workingDirectory);
            foreach ($lockData['packages'] as $package) {
                if ($package['name'] == $packageName) {
                    $version = $package['version'];

                    return $this->readComposerVersion($version);
                }
            }
            foreach ($lockData['packages-dev'] as $package) {
                if ($package['name'] == $packageName) {
                    $version = $package['version'];

                    return $this->readComposerVersion($version);
                }
            }
        } catch (FileNotFoundException $e) {
            $requires = parent::get($workingDirectory, 'require', []);
            if (\array_key_exists($packageName, $requires)) {
                $version = $requires[$packageName];

                return $this->readComposerVersion($version);
            }
            $devRequires = parent::get($workingDirectory, 'require-dev', []);
            if (\array_key_exists($packageName, $devRequires)) {
                $version = $devRequires[$packageName];

                return $this->readComposerVersion($version);
            }
        }

        return $default;
    }

    /**
     * @param string $workingDirectory
     * @param string $infoPath
     * @param bool   $default
     *
     * @return array|bool|mixed
     */
    public function read(string $workingDirectory, string $infoPath, $default = false)
    {
        $keys = explode('.', $infoPath);
        try {
            $current = $this->getComposerLockConfig($workingDirectory);
        } catch (FileNotFoundException $e) {
            $current = [];
        }
        foreach ($keys as $key) {
            if (!\is_array($current) || !\array_key_exists($key, $current)) {
                return parent::get($workingDirectory, $infoPath, $default);
            }
            $current = $current[$key];
        }

        return $current;
    }

    /**
     * @param $workingDirectory
     *
     * @throws FileNotFoundException
     *
     * @return mixed
     */
    protected function getComposerLockConfig(string $workingDirectory)
    {
        if (!\array_key_exists($workingDirectory, $this->composerLockConfig)) {
            $composerJsonPath = $workingDirectory . '/composer.lock';
            if (!$this->fileSystem->exists($composerJsonPath)) {
                throw new FileNotFoundException(sprintf(
                    'The composer.lock doesn\'t exist in the %s directory!',
                    $workingDirectory
                ));
            }

            $this->composerLockConfig[$workingDirectory] = json_decode(file_get_contents($composerJsonPath), true);
        }

        return $this->composerLockConfig[$workingDirectory];
    }
}
