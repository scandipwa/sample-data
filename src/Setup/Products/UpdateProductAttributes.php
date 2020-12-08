<?php

/**
 * ScandiPWA_SampleData
 *
 * @category    Scandiweb
 * @package     ScandiPWA_SampleData
 * @author      Vadims Petrovs <info@scandiweb.com>
 * @copyright   Copyright (c) 2020 Scandiweb, Ltd (https://scandiweb.com)
 */

namespace ScandiPWA\SampleData\Setup\Products;

use Magento\Catalog\Model\Product;
use Magento\Framework\Setup\SetupInterface;
use ScandiPWA\SampleData\Helper\FileParser;
use Magento\Eav\Setup\EavSetupFactory;

class UpdateProductAttributes
{
    const PATH = 'products/attribute-update.json';

    /**
     * @var FileParser
     */
    private $fileParser;

    /**
     * @var EavSetupFactory
     */
    private $eavSetupFactory;

    /**
     * @param FileParser $fileParser
     * @param EavSetupFactory $eavSetupFactory
     */
    public function __construct(
        FileParser $fileParser,
        EavSetupFactory $eavSetupFactory

    ){
        $this->fileParser = $fileParser;
        $this->eavSetupFactory = $eavSetupFactory;
    }

    /**
     * Applies migration.
     *
     * @param SetupInterface $setup
     */
    public function apply(SetupInterface $setup = null)
    {
        foreach ($this->fileParser->getJSONContent(self::PATH) as $attributeCode => $data) {
            if ($data) {
                $eavSetup = $this->eavSetupFactory->create(['setup' => $setup]);
                $eavSetup->updateAttribute(Product::ENTITY, $attributeCode, $data);
            }
        }
    }
}
