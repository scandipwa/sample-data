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

use Magento\Framework\Setup\SetupInterface;
use Magento\Catalog\Model\CategoryFactory;
use Magento\Catalog\Model\ProductFactory;
use ScandiPWA\SampleData\Helper\FileParser;

class CreateProducts
{
    const PATH = 'products/products.json';

    /**
     * @var FileParser
     */
    private $fileParser;

    /**
     * @var ProductFactory
     */
    private $productFactory;

    /**
     * @var CategoryFactory
     */
    private $categoryFactory;

    /**
     * @param FileParser $fileParser
     * @param CategoryFactory $categoryFactory
     * @param ProductFactory $productFactory
     */
    public function __construct(
        FileParser $fileParser,
        CategoryFactory $categoryFactory,
        ProductFactory $productFactory
    ){
        $this->fileParser = $fileParser;
        $this->categoryFactory = $categoryFactory;
        $this->productFactory = $productFactory;
    }

    /**
     * @inheritDoc
     */
    public function apply(SetupInterface $setup = null)
    {
        foreach ($this->fileParser->getJSONContent(self::PATH) as $data) {
            $categoryIds = [];

            $categories = $this->categoryFactory->create()
                ->getCollection()
                ->addAttributeToFilter('url_key', ['in' => $data['categories']])
                ->addAttributeToSelect('entity_id');

            foreach ($categories as $category) {
                $categoryIds[] = $category->getEntityId();
            }

            $product = $this->productFactory->create();

            if ($data['type_id'] === 'configurable') {
                $colorAttributeId = $product->getResource()->getAttribute('color')->getId();

                var_dump($colorAttributeId); die;
            }

            $product->setSku($data['sku']);
            $product->setName($data['name']);
            $product->setAttributeSetId($data['attribute_set_id']);
            $product->setStatus($data['status']);
            $product->setVisibility($data['visibility']);
            $product->setTaxClassId($data['tax_class']);
            $product->setTypeId($data['type_id']);

            if (isset($data['price'])) {
                $product->setPrice($data['price']); // price of product
            }

            $product->setCategoryIds($categoryIds);
            $product->setWebsiteIds($data['website_ids']);

            if (isset($data['qty'])) {
                $product->setStockData(
                    [
                        'use_config_manage_stock' => 0,
                        'manage_stock' => 1,
                        'is_in_stock' => 1,
                        'qty' => $data['qty']
                    ]
                );
            }

            foreach ($data['images'] as $imagePath) {
                $imagePath = $imagePath;
                // $product->addImageToMediaGallery($imagePath, ['image', 'small_image', 'thumbnail'], false, false);
            }

           // $product->save();
        }

        die;
    }

}
