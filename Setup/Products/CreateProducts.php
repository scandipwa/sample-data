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
            $colorAttribute = $product->getResource()->getAttribute('color');
            $sizeAttribute = $product->getResource()->getAttribute('size');

            if (isset($data['color'])) {
                $colorOptionValue = $colorAttribute->getSource()->getOptionId($data['color']);
                $product->setData('color', $colorOptionValue);
            }

            if (isset($data['size'])) {
                $sizeOptionValue = $sizeAttribute->getSource()->getOptionId($data['size']);
                $product->setData('size', $sizeOptionValue);
            }

            if ($data['type_id'] === 'configurable') {
                $colorAttributeId = $colorAttribute->getId();
                $sizeAttributeId = $sizeAttribute->getId();
                $attributes = [$colorAttributeId, $sizeAttributeId];
                $associatedProductIds = [2,4,5,6]; //Product Ids Of Associated Products
                foreach ($attributes as $attributeId) {
                    $data = array('attribute_id' => $attributeId, 'product_id' => $productId, 'position' => $position);
                    $position++;
                    $attributeModel->setData($data)->save();
                }
            }

             //name in Default Store View


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
