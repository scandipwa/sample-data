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

use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Framework\Setup\UpgradeDataInterface;

abstract class AbstractUpgradeData implements UpgradeDataInterface
{
    use UpgradeTrait;

    /**
     * @inheritDoc
     */
    public function upgrade(ModuleDataSetupInterface $setup, ModuleContextInterface $context)
    {
        $this->run($setup, $context);
    }
}