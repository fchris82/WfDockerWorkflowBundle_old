<?php declare(strict_types=1);
/**
 * Created by IntelliJ IDEA.
 * User: chris
 * Date: 2019.01.16.
 * Time: 12:50
 */

namespace Wf\DockerWorkflowBundle\Tests\Wizard;

use PHPUnit\Framework\TestCase;
use Wf\DockerWorkflowBundle\Exception\ConfigurationItemNotFoundException;
use Wf\DockerWorkflowBundle\Tests\Resources\Wizard\Configuration\Base1Wizard;
use Wf\DockerWorkflowBundle\Tests\Resources\Wizard\Configuration\Base2Wizard;
use Wf\DockerWorkflowBundle\Tests\Resources\Wizard\Configuration\DisabledWizard;
use Wf\DockerWorkflowBundle\Tests\Resources\Wizard\Configuration\MissingWizard;
use Wf\DockerWorkflowBundle\Wizard\Configuration;
use Wf\DockerWorkflowBundle\Wizard\ConfigurationItem;
use Wf\DockerWorkflowBundle\Wizards\BaseWizard;

class ConfigurationTest extends TestCase
{
    const RESOURCE_PATH = '/../Resources/Wizard/Configuration/';
    const TEST_PATH = '/tmp/wizard_config_test.yml';

    /**
     * @param string $file
     * @param array  $orderedClasses
     *
     * @dataProvider getGetAllEnabled
     */
    public function testGetAllEnabled($file, $orderedClasses)
    {
        $configuration = new Configuration(__DIR__ . static::RESOURCE_PATH . $file);
        $enabledItems = $configuration->getAllEnabled();
        $wizardClasses = [];
        foreach ($enabledItems as $item) {
            $wizardClasses[] = $item->getClass();
        }

        $this->assertEquals($orderedClasses, $wizardClasses);
    }

    public function getGetAllEnabled(): array
    {
        return [
            ['empty.yml', []],
            ['base.yml', [Base1Wizard::class, Base2Wizard::class]],
            ['changed_priority.yml', [Base2Wizard::class, Base1Wizard::class]],
        ];
    }

    /**
     * @param string $file
     * @param array  $orderedClasses
     *
     * @dataProvider dpGetConfigurationList
     */
    public function testGetConfigurationList($file, $orderedClasses)
    {
        $configuration = new Configuration(__DIR__ . static::RESOURCE_PATH . $file);
        $enabledItems = $configuration->getConfigurationList();
        $wizardClasses = [];
        foreach ($enabledItems as $item) {
            $wizardClasses[] = $item->getClass();
        }

        $this->assertEquals($orderedClasses, $wizardClasses);
    }

    public function dpGetConfigurationList(): array
    {
        return [
            ['empty.yml', []],
            ['base.yml', [Base1Wizard::class, Base2Wizard::class, DisabledWizard::class]],
            ['changed_priority.yml', [Base2Wizard::class, Base1Wizard::class, DisabledWizard::class]],
        ];
    }

    /**
     * @param string      $initFile
     * @param array       $set
     * @param array       $add
     * @param array       $remove
     * @param string|null $changeType
     * @param array       $result
     * @param string      $resultFile
     *
     * @throws \ReflectionException
     *
     * @dataProvider dpChanges
     */
    public function testChanges(
        string $initFile,
        array $set,
        array $add,
        array $remove,
        $changeType,
        array $result,
        string $resultFile
    ) {
        $configuration = new Configuration(__DIR__ . static::RESOURCE_PATH . $initFile);
        // Read current information. It is important if you don't do anything (add/set/remove).
        $configuration->getConfigurationList();
        foreach ($set as $setItem) {
            $configuration->set($setItem);
        }
        foreach ($add as $addItem) {
            $configuration->add($addItem);
        }
        foreach ($remove as $removeItem) {
            $configuration->remove($removeItem);
        }
        $this->assertEquals(\count($result) > 0, $configuration->hasChanges($changeType));

        $changes = $configuration->getChanges($changeType);
        $this->assertEquals($result, $changes);

        $reflectionClass = new \ReflectionClass($configuration);
        $reflectionProp = $reflectionClass->getProperty('configurationFilePath');
        $reflectionProp->setAccessible(true);
        $reflectionProp->setValue($configuration, static::TEST_PATH);
        $configuration->saveConfigurationList();
        $this->assertEquals(
            trim(file_get_contents(__DIR__ . static::RESOURCE_PATH . $resultFile)),
            trim(file_get_contents(static::TEST_PATH))
        );
        file_put_contents(static::TEST_PATH, '');
    }

    public function dpChanges(): array
    {
        $base1Wizard = new Base1Wizard();
        $base1Item = new ConfigurationItem($base1Wizard, $base1Wizard->getDefaultName(), !$base1Wizard->isHidden(), 'Builder');
        $missingItem = new ConfigurationItem(MissingWizard::class, 'Missing class');

        return [
            [ // 0
                'empty.yml',        // initFile
                [],                 // set
                [],                 // add
                [],                 // remove
                null,               // changeType
                [],                 // result
                'result_empty.yml',  // resultFile
            ],
            [ // 1
                'empty.yml',                    // initFile
                [],                             // set
                [],                             // add
                [],                             // remove
                Configuration::CHANGES_ADDED,   // changeType
                [],                             // result
                'result_empty.yml',              // resultFile
            ],
            [ // 2
                'empty.yml',                    // initFile
                [],                             // set
                [],                             // add
                [],                             // remove
                Configuration::CHANGES_UPDATED, // changeType
                [],                             // result
                'result_empty.yml',             // resultFile
            ],
            [ // 3
                'empty.yml',                    // initFile
                [],                             // set
                [],                             // add
                [],                             // remove
                Configuration::CHANGES_REMOVED, // changeType
                [],                             // result
                'result_empty.yml',             // resultFile
            ],
            [ // 4
                'empty.yml',                    // initFile
                [],                             // set
                [$missingItem],                 // add
                [],                             // remove
                null,                           // changeType
                [                               // result
                    Configuration::CHANGES_ADDED => [$missingItem],
                ],
                'result_only_missing.yml',      // resultFile
            ],
            [ // 5
                'base.yml',         // initFile
                [],                 // set
                [],                 // add
                [],                 // remove
                null,               // changeType
                [],                 // result
                'result_base.yml',  // resultFile
            ],
            [ // 6
                'base.yml',
                [$base1Item],
                [$missingItem],
                [],
                null,
                [
                    Configuration::CHANGES_UPDATED => [$base1Item],
                    Configuration::CHANGES_ADDED => [$missingItem],
                ],
                'result_set_add.yml',
            ],
            [ // 7
                'base.yml',                     // initFile
                [$base1Item],                   // set
                [$missingItem],                 // add
                [],                             // remove
                Configuration::CHANGES_ADDED,   // changeType
                [$missingItem],                 // result
                'result_set_add.yml',           // resultFile
            ],
            [ // 8
                'base.yml',                     // initFile
                [$base1Item],                   // set
                [$missingItem],                 // add
                [],                             // remove
                Configuration::CHANGES_UPDATED, // changeType
                [$base1Item],                   // result
                'result_set_add.yml',           // resultFile
            ],
            [ // 9
                'base.yml',                     // initFile
                [$base1Item],                   // set
                [$missingItem],                 // add
                [],                             // remove
                Configuration::CHANGES_REMOVED, // changeType
                [],                             // result
                'result_set_add.yml',           // resultFile
            ],
            // Delete by item
            [ // 10
                'base.yml',                     // initFile
                [],                             // set
                [],                             // add
                [$base1Item],                   // remove
                null,                           // changeType
                [                               // result
                    Configuration::CHANGES_REMOVED => [$base1Item],
                ],
                'result_removed_base1.yml',     // resultFile
            ],
            // Delete by object
            [ // 11
                'base.yml',                     // initFile
                [],                             // set
                [],                             // add
                [$base1Wizard],                 // remove
                null,                           // changeType
                [                               // result
                    Configuration::CHANGES_REMOVED => [$base1Item],
                ],
                'result_removed_base1.yml',     // resultFile
            ],
            // Delete by class name
            [ // 12
                'base.yml',                     // initFile
                [],                             // set
                [],                             // add
                [Base1Wizard::class],           // remove
                null,                           // changeType
                [                               // result
                    Configuration::CHANGES_REMOVED => [$base1Item],
                ],
                'result_removed_base1.yml',     // resultFile
            ],
            [ // 13
                'base.yml',                      // initFile
                [],                              // set
                [],                              // add
                [$base1Item],                    // remove
                Configuration::CHANGES_REMOVED,  // changeType
                [$base1Item],                    // result
                'result_removed_base1.yml',      // resultFile
            ],
        ];
    }

    /**
     * @param string                $file
     * @param string|object         $class
     * @param \Exception|BaseWizard $response
     *
     * @dataProvider dpGet
     *
     * @throws ConfigurationItemNotFoundException
     */
    public function testGet($file, $class, $response)
    {
        $configuration = new Configuration(__DIR__ . static::RESOURCE_PATH . $file);

        if ($response instanceof \Exception) {
            $this->expectException(\get_class($response));
            $this->assertFalse($configuration->has($class));
            $configuration->get($class);
        } else {
            $this->assertTrue($configuration->has($class));
            $configItem = $configuration->get($class);
            $this->assertEquals($response, $configItem->getClass());
        }
    }

    public function dpGet(): array
    {
        $base1Wizard = new Base1Wizard();

        return [
            ['empty.yml', '', new ConfigurationItemNotFoundException('none')],
            ['empty.yml', Base1Wizard::class, new ConfigurationItemNotFoundException('none')],
            ['base.yml', Base1Wizard::class, Base1Wizard::class],
            ['base.yml', $base1Wizard, Base1Wizard::class],
            ['base.yml', MissingWizard::class, new ConfigurationItemNotFoundException('none')],
        ];
    }
}
