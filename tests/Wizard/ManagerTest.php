<?php declare(strict_types=1);
/**
 * Created by IntelliJ IDEA.
 * User: chris
 * Date: 2019.01.17.
 * Time: 10:18
 */

namespace Wf\DockerWorkflowBundle\Tests\Wizard;

use PHPUnit\Framework\TestCase;
use Wf\DockerWorkflowBundle\Tests\Resources\Wizard\Configuration\Base1Wizard;
use Wf\DockerWorkflowBundle\Tests\Resources\Wizard\Configuration\Base2Wizard;
use Wf\DockerWorkflowBundle\Tests\Resources\Wizard\Configuration\DisabledWizard;
use Wf\DockerWorkflowBundle\Tests\Resources\Wizard\Configuration\MissingWizard;
use Wf\DockerWorkflowBundle\Wizard\Configuration;
use Wf\DockerWorkflowBundle\Wizard\ConfigurationItem;
use Wf\DockerWorkflowBundle\Wizard\Manager;
use Wf\DockerWorkflowBundle\Wizard\WizardInterface;

class ManagerTest extends TestCase
{
    /**
     * @param WizardInterface[]|array $wizards
     * @param string                  $class
     * @param \Exception|string       $result
     *
     * @throws \Exception
     * @dataProvider dpGetWizard
     */
    public function testGetWizard($wizards, string $class, $result)
    {
        $configuration = new Configuration(__DIR__ . ConfigurationTest::RESOURCE_PATH . 'empty.yml');
        $manager = new Manager($configuration);

        foreach ($wizards as $wizard) {
            $manager->addWizard($wizard);
        }

        if ($result instanceof \Exception) {
            $this->expectException(\get_class($result));
        }
        $wizards = $manager->getWizard($class);
        if (!$result instanceof \Exception) {
            $this->assertEquals($result, \get_class($wizards));
        }
    }

    public function dpGetWizard(): array
    {
        $base1Wizard = new Base1Wizard();
        $base2Wizard = new Base2Wizard();
        $base = [
            $base1Wizard,
            $base2Wizard,
        ];

        return [
            [[], '', new \Exception()],
            [[], Base1Wizard::class, new \Exception()],
            [$base, Base1Wizard::class, Base1Wizard::class],
            [$base, Base2Wizard::class, Base2Wizard::class],
            [$base, MissingWizard::class, new \Exception()],
        ];
    }

    /**
     * @param string                  $baseConfigFile
     * @param WizardInterface[]|array $availableWizards
     * @param string[]|array          $unsavedChanges
     * @param array                   $result
     *
     * @dataProvider dpSyncConfiguration
     */
    public function testSyncConfiguration($baseConfigFile, $availableWizards, $unsavedChanges, $result)
    {
        $configuration = new Configuration(__DIR__ . ConfigurationTest::RESOURCE_PATH . $baseConfigFile);
        $manager = new Manager($configuration);

        foreach ($availableWizards as $wizard) {
            $manager->addWizard($wizard);
        }

        $manager->syncConfiguration();

        $this->assertEquals($unsavedChanges, $manager->getConfigurationUnsavedChanges());
        $wizardClasses = array_map(function (ConfigurationItem $item) {
            return $item->getClass();
        }, $manager->getConfiguration()->getConfigurationList());
        $this->assertEquals($result, $wizardClasses);
        $this->assertEquals($result, array_keys($manager->getAllWizards()));
    }

    public function dpSyncConfiguration()
    {
        $base1Wizard = new Base1Wizard();
        $missingWizard = new MissingWizard();
        $disabledWizard = new DisabledWizard();
        $base1ConfigurationItem1 = new ConfigurationItem(Base1Wizard::class, 'Base 1 Wizard', true, '');
        $base1ConfigurationItem2 = new ConfigurationItem(Base1Wizard::class, 'Base 1 Wizard', true, 'Builder');
        $base2ConfigurationItem = new ConfigurationItem(Base2Wizard::class, 'Changed Name Wizard', true, 'Builder');
        $disabledConfigurationItem = new ConfigurationItem(DisabledWizard::class, 'Disabled Wizard', false, 'Disabled');
        $missingConfigurationItem = new ConfigurationItem(MissingWizard::class, 'Missing Wizard', true, '');

        return [
            ['empty.yml', [], [], []],
            [
                'empty.yml',
                [$base1Wizard],
                [Configuration::CHANGES_ADDED => [$base1ConfigurationItem1]],
                [Base1Wizard::class],
            ],
            ['base.yml', [], [Configuration::CHANGES_REMOVED => [
                $base1ConfigurationItem2,
                $base2ConfigurationItem,
                $disabledConfigurationItem,
            ]], []],
            [
                'base.yml',
                [$disabledWizard, $missingWizard],
                [
                    Configuration::CHANGES_ADDED => [$missingConfigurationItem],
                    Configuration::CHANGES_REMOVED => [
                        $base1ConfigurationItem2,
                        $base2ConfigurationItem,
                    ],
                ],
                [DisabledWizard::class, MissingWizard::class],
            ],
        ];
    }

    /**
     * @param string                  $baseConfigFile
     * @param array|WizardInterface[] $availableWizards
     * @param array|string[]          $statuses
     *
     * @dataProvider dpWizardIs
     */
    public function testWizardIs($baseConfigFile, $availableWizards, $statuses)
    {
        $configuration = new Configuration(__DIR__ . ConfigurationTest::RESOURCE_PATH . $baseConfigFile);
        $manager = new Manager($configuration);

        foreach ($availableWizards as $wizard) {
            $manager->addWizard($wizard);
        }

        foreach ($statuses as $class => $status) {
            if (\is_array($status)) {
                $class = $status['object'];
                $status = $status['status'];
            }
            switch ($status) {
                case Configuration::CHANGES_ADDED:
                    $this->assertTrue($manager->wizardIsNew($class));
                    $this->assertFalse($manager->wizardIsUpdated($class));
                    $this->assertFalse($manager->wizardIsRemoved($class));
                    break;
                case Configuration::CHANGES_UPDATED:
                    $this->assertFalse($manager->wizardIsNew($class));
                    $this->assertTrue($manager->wizardIsUpdated($class));
                    $this->assertFalse($manager->wizardIsRemoved($class));
                    break;
                case Configuration::CHANGES_REMOVED:
                    $this->assertFalse($manager->wizardIsNew($class));
                    $this->assertFalse($manager->wizardIsUpdated($class));
                    $this->assertTrue($manager->wizardIsRemoved($class));
                    break;
            }
        }
    }

    public function dpWizardIs()
    {
        $base1Wizard = new Base1Wizard();

        return [
            ['empty.yml', [$base1Wizard], [Base1Wizard::class => Configuration::CHANGES_ADDED]],
            ['base.yml', [], [
                // Test with Wizard object
                Base1Wizard::class => [
                    'object' => $base1Wizard,
                    'status' => Configuration::CHANGES_REMOVED,
                ],
                // Test with ConfigurationItem object
                Base2Wizard::class => [
                    'object' => new ConfigurationItem(Base2Wizard::class, 'Base Wizard'),
                    'status' => Configuration::CHANGES_REMOVED,
                ],
                // Test with name
                DisabledWizard::class => Configuration::CHANGES_REMOVED,
            ]],
        ];
    }
}
