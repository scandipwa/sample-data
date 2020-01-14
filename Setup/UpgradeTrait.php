<?php
/**
 * ScandiPWA_SampleData
 *
 * @category    Scandiweb
 * @package     ScandiPWA_SampleData
 * @author      Vadims Petrovs <info@scandiweb.com>
 * @copyright   Copyright (c) 2020 Scandiweb, Ltd (https://scandiweb.com)
 */
namespace ScandiPWA\SampleData\Setup;

use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\ObjectManagerInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\App\State;
use Magento\Framework\Setup\SetupInterface;
use Traversable;

trait UpgradeTrait
{
    /**
     * @var ModuleContextInterface
     */
    protected $context;

    /**
     * Migration list.
     *
     * @var array
     */
    protected $migrations = [];

    /**
     * @var ObjectManagerInterface
     */
    protected $objectManager;

    /**
     * @var bool
     */
    protected $outputEnabled = true;

    /**
     * AbstractSetup constructor.
     * @param ObjectManagerInterface $objectManager
     * @param State $state
     */
    public function __construct(ObjectManagerInterface $objectManager, State $state)
    {
        try {
            $state->getAreaCode();
        } catch (LocalizedException $e) {
            $state->setAreaCode('adminhtml');
        }

        $this->objectManager = $objectManager;
    }

    /**
     * @inheritDoc
     */
    public function run(SetupInterface $setup, ModuleContextInterface $context)
    {
        $setup->startSetup();
        $this->context = $context;

        foreach ($this->getMigrations() as $version => $migration) {
            if ($this->getVersion($version)) {
                $migration->apply($setup);
            }
        }

        $setup->endSetup();
    }

    /**
     * Check if provided version is the current module version.
     *
     * @param string $version
     *
     * @return bool
     */
    protected function getVersion($version)
    {
        if (version_compare($this->context->getVersion(), $version, '<')) {
            if ($this->outputEnabled) {
                echo PHP_EOL . "\033[35mMigration version " . $version . " \033[0m" . PHP_EOL;
            }

            return true;
        }

        return false;
    }

    /**
     * @throws LocalizedException
     */
    protected function getMigrations(): Traversable
    {
        if (empty($this->migrations)) {
            throw new LocalizedException(__('Class attribute \'migrations\' must be populated'));
        }

        foreach ($this->migrations as $version => $migration) {
            $migration = $this->objectManager->get($migration);
            yield $version => $migration;
        }
    }
}