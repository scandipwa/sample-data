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
            'catalog/seo/category_canonical_tag' => 1,
            'catalog/seo/product_canonical_tag' => 1,
            'content_customization/cart_content/cart_cms' => 'cartpage-promo',
            'content_customization/footer_content/footer_cms' => 'footer-links',
            'content_customization/header_content/contacts_cms' => 'header-contacts-above',
            'content_customization/header_content/header_cms' => 'header-contacts-bellow',
            'content_customization/minicart_content/minicart_cms' => 'cartoverlay-promo',
            'design/header/logo_src' => 'default/logo.png',
            'design/header/logo_width' => 140,
            'design/header/logo_height' => 25,
            'design/theme/theme_id' => '4',
            'web/seo/use_rewrites' => 1
        ];

        foreach ($configData as $path => $value) {
            $this->config->saveConfig($path, $value, self::SCOPE_DEFAULT);
        }

    }

}
