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

use ScandiPWA\SampleData\Setup\System\SetConfig;
use ScandiPWA\SampleData\Setup\Categories\CreateCategories;
use ScandiPWA\SampleData\Setup\Products\CreateProducts;
use ScandiPWA\SampleData\Setup\Products\CreateProductAttributes;
use ScandiPWA\SampleData\Setup\CMS\Page\AddHomePage;
use ScandiPWA\SampleData\Setup\CMS\Block\AddHomepageBlocks;
use ScandiPWA\SampleData\Setup\System\CreateMenu;
use ScandiPWA\SampleData\Setup\AbstractUpgradeData;

class UpgradeData extends AbstractUpgradeData
{
    protected $migrations = [
        '0.0.1' => SetConfig::class,
        '0.0.2' => AddHomePage::class,
        '0.0.3' => AddHomepageBlocks::class,
        '0.0.4' => CreateCategories::class,
        '0.0.5' => CreateProductAttributes::class,
        '0.0.6' => CreateProducts::class,
        '0.0.7' => CreateMenu::class
    ];
}
