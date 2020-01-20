<?php

/**
 * ScandiPWA_SampleData
 *
 * @category    Scandiweb
 * @package     ScandiPWA_SampleData
 * @author      Vadims Petrovs <info@scandiweb.com>
 * @copyright   Copyright (c) 2020 Scandiweb, Ltd (https://scandiweb.com)
 */

namespace ScandiPWA\SampleData\Setup\System;

use Magento\Framework\Setup\SetupInterface;
use Magento\Config\Model\ResourceModel\Config;

class SetConfig
{
    const SCOPE_DEFAULT = 'default';

    /**
     * @var Config
     */
    protected $config;

    /**
     * @param Config $config
     */
    public function __construct(
        Config $config
    )
    {
        $this->config = $config;
    }

    /**
     * Applies migration.
     *
     * @param SetupInterface $setup
     */
    public function apply(SetupInterface $setup = null)
    {
        $configData = [
            'design/theme/theme_id' => '4',
//            'system/full_page_cache/caching_application' => '2',
//            'system/full_page_cache/varnish/access_list' => '127.0.0.1, 172.0.0.0, app',
//            'system/full_page_cache/varnish/backend_host' => 'nginx',
//            'system/full_page_cache/varnish/backend_port' => '80',
//            'system/full_page_cache/varnish/grace_period' => '300',
        ];

        foreach ($configData as $path => $value) {
            $this->config->saveConfig($path, $value, self::SCOPE_DEFAULT);
        }

    }

}
