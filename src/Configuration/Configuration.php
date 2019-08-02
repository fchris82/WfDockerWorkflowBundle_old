<?php declare(strict_types=1);
/**
 * Created by IntelliJ IDEA.
 * User: chris
 * Date: 2018.03.10.
 * Time: 16:57
 */

namespace Wf\DockerWorkflowBundle\Configuration;

use Symfony\Component\Config\Definition\Builder\ArrayNodeDefinition;
use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;
use Symfony\Component\Config\Definition\Exception\InvalidConfigurationException;
use Symfony\Component\Config\Definition\Processor;
use Symfony\Component\Config\Exception\FileLoaderImportCircularReferenceException;
use Symfony\Component\EventDispatcher\EventDispatcher;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Yaml\Parser;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;
use Wf\DockerWorkflowBundle\Event\Configuration\PreProcessConfigurationEvent;
use Wf\DockerWorkflowBundle\Event\ConfigurationEvents;
use Wf\DockerWorkflowBundle\Exception\InvalidWfVersionException;
use Wf\DockerWorkflowBundle\Exception\RecipeHasNotConfigurationException;
use Wf\DockerWorkflowBundle\Recipes\BaseRecipe;
use Wf\DockerWorkflowBundle\Recipes\HiddenRecipe;
use Wf\DockerWorkflowBundle\Recipes\SystemRecipe;

class Configuration implements ConfigurationInterface
{
    const ROOT_NODE = 'project';

    /**
     * @var RecipeManager
     */
    protected $recipeManager;

    /**
     * @var Filesystem
     */
    protected $filesystem;

    /**
     * @var EventDispatcherInterface
     */
    protected $eventDispatcher;

    /**
     * @var Parser
     */
    protected $ymlParser;

    /**
     * @var array|string[]
     */
    protected $importCache = [];

    /**
     * Configuration constructor.
     *
     * @param RecipeManager            $recipeManager
     * @param Filesystem               $filesystem
     * @param EventDispatcherInterface $eventDispatcher
     */
    public function __construct(RecipeManager $recipeManager, Filesystem $filesystem, EventDispatcherInterface $eventDispatcher)
    {
        $this->recipeManager = $recipeManager;
        $this->filesystem = $filesystem;
        $this->eventDispatcher = $eventDispatcher;
    }

    /**
     * @param string      $configFile
     * @param string|null $pwd
     * @param string|null $wfVersion
     *
     * @throws FileLoaderImportCircularReferenceException
     * @throws InvalidWfVersionException
     *
     * @return array
     */
    public function loadConfig(string $configFile, string $pwd = null, string $wfVersion = null): array
    {
        if (null === $pwd) {
            $pwd = \dirname($configFile);
        }
        $ymlFilePath = $this->filesystem->exists($configFile) && is_file($configFile)
            ? $configFile
            : $pwd . '/' . $configFile;
        $baseConfig = $this->readConfig($ymlFilePath);

        $event = new PreProcessConfigurationEvent($baseConfig, $pwd, $wfVersion);
        $this->eventDispatcher->dispatch($event, ConfigurationEvents::PRE_PROCESS_CONFIGURATION);

        $processor = new Processor();
        $fullConfig = $processor->processConfiguration($this, [self::ROOT_NODE => $event->getConfig()]);

        // Check the WF version is correct!
        $this->validateWfVersion($fullConfig, $wfVersion);

        return $fullConfig;
    }

    /**
     * Generates the configuration tree builder.
     *
     * @return TreeBuilder The tree builder
     */
    public function getConfigTreeBuilder(): TreeBuilder
    {
        $treeBuilder = new TreeBuilder(self::ROOT_NODE);
        /** @var ArrayNodeDefinition $rootNode */
        $rootNode = $treeBuilder->getRootNode();

        $rootNode
            ->children()
                ->arrayNode('imports')
                    ->info('<comment>You can import some other <info>yml</info> files.</comment>')
                    ->example(['.wf.base.yml'])
                    ->scalarPrototype()->end()
                    ->defaultValue([])
                ->end()
                ->arrayNode('version')
                    ->info('<comment>Which WF Makefile version do you want to use? You can combine it with the minimum WF version with the <info>@</info> symbol: <info>[base]@[wf_minimum_version]</info></comment>')
                    ->example('2.0.0@2.198')
                    ->addDefaultsIfNotSet()
                    ->isRequired()
                    ->children()
                        ->scalarNode('base')
                            ->info('<comment>Which WF Makefile version do you want to use?</comment>')
                            ->example('2.0.0')
                            ->isRequired()
                            ->cannotBeEmpty()
                        ->end()
                        ->scalarNode('wf_minimum_version')
                            ->info('<comment>You can set what is the minimum WF version.</comment>')
                            ->example('2.198')
                            ->defaultNull()
                        ->end()
                    ->end()
                    ->beforeNormalization()
                        ->ifString()
                        ->then(function ($v) {
                            // @ - the first character - needs to avoid "Notice: Undefined offset: 1" error if the $v contains only base version: "2.0.0"
                            @list($base, $wfMinimumVersion) = explode('@', $v, 2);

                            return [
                                'base' => $base,
                                'wf_minimum_version' => $wfMinimumVersion,
                            ];
                        })
                    ->end()
                ->end()
                ->scalarNode('name')
                    ->info('<comment>You have to set a name for the project. Avoid special characters and spaces, you should use latin characters and numbers, otherwise you can encounter problems.</comment>')
                    ->isRequired()
                    ->cannotBeEmpty()
                    ->validate()
                        ->ifTrue(function ($v) {
                            return preg_match('/[^a-zA-Z0-9\-_\.]/', $v);
                        })
                        ->thenInvalid('Avoid special characters and spaces, you should use latin characters and numbers, otherwise you can encounter problems.')
                    ->end()
                ->end()
                ->scalarNode('docker_data_dir')
                    ->info('<comment>You can set an alternative docker data directory.</comment>')
                    ->cannotBeEmpty()
                    ->defaultValue('%wf.target_directory%/.data')
                ->end()
            ->end()
        ;

        // Add system recipes nodes
        $this->addSystemRecipeNodes($rootNode);
        // Add recipe nodes
        $rootNode->append($this->addRecipesNode());

        return $treeBuilder;
    }

    protected function getYmlParser(): Parser
    {
        if (!$this->ymlParser) {
            $this->ymlParser = new Parser();
        }

        return $this->ymlParser;
    }

    /**
     * @param string $ymlFilePath
     *
     * @throws FileLoaderImportCircularReferenceException
     *
     * @return array
     */
    protected function readConfig($ymlFilePath): array
    {
        $baseConfig = $this->getYmlParser()->parseFile($ymlFilePath);
        $baseConfig = $this->handleImports($baseConfig, $ymlFilePath);

        return $baseConfig;
    }

    /**
     * Check that the current installed WF version is compatibel with this project or you have to upgrade!
     *
     * @param array       $config
     * @param string|null $wfVersion
     *
     * @throws InvalidWfVersionException
     */
    protected function validateWfVersion(array $config, ?string $wfVersion): void
    {
        // We call the loadConfiguration from Wizard too, and then it isn't important this
        if (null === $wfVersion) {
            return;
        }

        $wfMinimumVersion = $config['version']['wf_minimum_version'];
        if ($wfMinimumVersion && version_compare($wfVersion, $wfMinimumVersion, '<')) {
            throw new InvalidWfVersionException(sprintf(
                '<error>You are using the <comment>%s</comment> version of WF, but the program needs at least' .
                ' <comment>%s</comment> version. You have to upgrade the wf with the <comment>wf -u</comment> command!</error>',
                $wfVersion,
                $wfMinimumVersion
            ));
        }
    }

    /**
     * @param array  $baseConfig
     * @param string $baseConfigYmlFullPath
     *
     * @throws FileLoaderImportCircularReferenceException
     *
     * @return array
     */
    protected function handleImports(array $baseConfig, string $baseConfigYmlFullPath): array
    {
        $sourceDirectory = \dirname($baseConfigYmlFullPath);
        if (\array_key_exists('imports', $baseConfig)) {
            // Ebbe gyűjtjük össze az import configokat.
            $fullImportConfig = [];
            foreach ($baseConfig['imports'] as $importYml) {
                if (!$this->filesystem->exists($importYml) || !is_file($importYml)) {
                    $importYmlAlt = $sourceDirectory . '/' . $importYml;
                    if (!$this->filesystem->exists($importYmlAlt) || !is_file($importYmlAlt)) {
                        throw new InvalidConfigurationException(sprintf('The `%s` and `%s` configuration file doesn\'t exist either!', $importYml, $importYmlAlt));
                    }

                    $importYml = $importYmlAlt;
                }

                $importYml = realpath($importYml);
                if (\in_array($importYml, $this->importCache)) {
                    $this->importCache[] = $importYml;
                    throw new FileLoaderImportCircularReferenceException($this->importCache);
                }
                $this->importCache[] = $importYml;

                $importConfig = $this->readConfig($importYml);
                // A később importált felülírja a korábbit.
                $fullImportConfig = $this->configDeepMerge($fullImportConfig, $importConfig);

                array_pop($this->importCache);
            }

            // A baseconfig-os felülírja az összes importosat
            $baseConfig = $this->configDeepMerge($fullImportConfig, $baseConfig);
        }

        return $baseConfig;
    }

    protected function configDeepMerge(array $baseConfig, array $overrideConfig): array
    {
        foreach ($overrideConfig as $key => $value) {
            if ($this->isConfigLeaf($value) || !\array_key_exists($key, $baseConfig)) {
                $baseConfig[$key] = $value;
            } else {
                $baseConfig[$key] = $this->configDeepMerge($baseConfig[$key], $value);
            }
        }

        return $baseConfig;
    }

    protected function isConfigLeaf($value): bool
    {
        // Not array or empty array
        if (!\is_array($value) || $value === []) {
            return true;
        }
        // It is a sequential array, like a list
        if (array_keys($value) === range(0, \count($value) - 1)) {
            return true;
        }

        return false;
    }

    /**
     * Register system recipes to root node.
     *
     * @param ArrayNodeDefinition $rootNode
     */
    protected function addSystemRecipeNodes(ArrayNodeDefinition $rootNode): void
    {
        foreach ($this->recipeManager->getRecipes() as $recipe) {
            if ($recipe instanceof SystemRecipe) {
                try {
                    $rootNode->append($recipe->getConfig());
                } catch (RecipeHasNotConfigurationException $e) {
                    // do nothing
                }
            }
        }
    }

    /**
     * Register recipes under the `recipes` node.
     *
     * @return ArrayNodeDefinition
     */
    protected function addRecipesNode(): ArrayNodeDefinition
    {
        $treeBuilder = new TreeBuilder('recipes');
        /** @var ArrayNodeDefinition $node */
        $node = $treeBuilder->getRootNode();
        $node
            ->info('<comment>The configs of recipes. If you want to disable one from import, set the false value!</comment>')
            ->beforeNormalization()
                ->always(function ($v) {
                    foreach ($v as $service => $value) {
                        if (false === $value) {
                            unset($v[$service]);
                        }
                    }

                    return $v ?: [];
                })
            ->end()
        ;

        /** @var BaseRecipe $recipe */
        foreach ($this->recipeManager->getRecipes() as $recipe) {
            if (!$recipe instanceof HiddenRecipe) {
                $node->append($recipe->getConfig());
            }
        }

        return $node;
    }
}
