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

use ScandiPWA\SampleData\Setup\Categories\CreateCategories;
use ScandiPWA\SampleData\Setup\Products\CreateProducts;
use ScandiPWA\SampleData\Setup\CMS\Page\AddAboutUsPage;
use ScandiPWA\SampleData\Setup\AbstractUpgradeData;

class UpgradeData extends AbstractUpgradeData
{
    protected $migrations = [
        '0.0.1' => AddAboutUsPage::class,
        '0.0.3' => CreateCategories::class,
        '0.0.4' => CreateProducts::class
    ];
}
