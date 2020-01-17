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
use Magento\ConfigurableProduct\Model\Product\Type\Configurable\Attribute;
use Magento\ConfigurableProduct\Model\Product\Type\Configurable;
use ScandiPWA\SampleData\Helper\FileParser;
use ScandiPWA\SampleData\Helper\MediaMigration;
use Magento\Catalog\Model\ProductLink\LinkFactory;
use Magento\Framework\App\Filesystem\DirectoryList;

class CreateProducts
{
    const PATH = 'products/products.json';
    const MIGRATION_MODULE = 'ScandiPWA_SampleData';
    const MEDIA_PATH = 'catalog/product';

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
     * @var Attribute
     */
    private $attribute;

    /**
     * @var Configurable
     */
    private $productTypeConfigurable;

    /**
     * @var MediaMigration
     */
    private $mediaHelper;

    /**
     * @var LinkFactory
     */
    private $productLinkFactory;

    /**
     * @var DirectoryList
     */
    private $directoryList;

    /**
     * @param FileParser $fileParser
     * @param CategoryFactory $categoryFactory
     * @param ProductFactory $productFactory
     * @param Attribute $attribute
     * @param Configurable $productTypeConfigurable
     * @param MediaMigration $mediaHelper
     * @param LinkFactory $productLinkFactory
     * @param DirectoryList $directoryList
     */
    public function __construct(
        FileParser $fileParser,
        CategoryFactory $categoryFactory,
        ProductFactory $productFactory,
        Attribute $attribute,
        Configurable $productTypeConfigurable,
        MediaMigration $mediaHelper,
        LinkFactory $productLinkFactory,
        DirectoryList $directoryList
    ){
        $this->fileParser = $fileParser;
        $this->categoryFactory = $categoryFactory;
        $this->productFactory = $productFactory;
        $this->attribute = $attribute;
        $this->productTypeConfigurable = $productTypeConfigurable;
        $this->mediaHelper = $mediaHelper;
        $this->productLinkFactory = $productLinkFactory;
        $this->directoryList = $directoryList;
    }

    /**
     * @inheritDoc
     */
    public function apply(SetupInterface $setup = null)
    {
        $files = [
            '1w1w1w1w_3.jpg',
            '4r4r5t5t_3.jpg',
            'b1_35.jpg',
            'b4_21.jpg'
        ];

        $this->mediaHelper->copyMediaFiles($files,self::MIGRATION_MODULE, self::MEDIA_PATH);

        foreach ($this->fileParser->getJSONContent(self::PATH) as $data) {
            $product = $this->productFactory->create();

            if ($product->loadByAttribute('sku', $data['sku']) !== false) {
                continue;
            }

            $categoryIds = [];

            $categories = $this->categoryFactory->create()
                ->getCollection()
                ->addAttributeToFilter('url_key', ['in' => $data['categories']])
                ->addAttributeToSelect('entity_id');

            foreach ($categories as $category) {
                $categoryIds[] = $category->getEntityId();
            }

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

            $product->setSku($data['sku']);
            $product->setName($data['name']);
            $product->setShortDescription($data['short_description']);
            $product->setDescription($this->fileParser->getHtmlContent($data['description']));
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

            $mediaUrl = $this->directoryList->getPath('media') . '/' . self:: MEDIA_PATH .'/';

            foreach ($data['images'] as $imagePath) {
                $product->addImageToMediaGallery($mediaUrl . $imagePath, ['image', 'small_image', 'thumbnail'], false, false);
            }

            $productId = $product->save()->getId();

            if ($data['type_id'] === 'configurable') {
                $colorAttributeId = $colorAttribute->getId();
                $sizeAttributeId = $sizeAttribute->getId();
                $attributes = [$colorAttributeId, $sizeAttributeId];
                $associatedProductIds = [];

                foreach ($data['associated_products'] as $associatedSku) {
                    $associatedProduct = $this->productFactory->create();
                    $associatedProductIds[] = $associatedProduct->loadByAttribute('sku', $associatedSku)->getId();
                }

                $position = 0;
                foreach ($attributes as $attributeId) {
                    $associatedData = ['attribute_id' => $attributeId, 'product_id' => $productId, 'position' => $position];
                    $position++;
                    $this->attribute->setData($associatedData)->save();
                }

                $product->setAffectConfigurableProductAttributes($data['attribute_set_id']);
                $this->productTypeConfigurable->setUsedProductAttributeIds($attributes, $product);
                $product->setNewVariationsAttributeSetId($data['attribute_set_id']);
                $product->setAssociatedProductIds($associatedProductIds);
                $product->setCanSaveConfigurableAttributes(true);
                $product->save();
            } elseif ($data['type_id'] === 'grouped') {
                $associated = [];
                $position = 0;

                foreach ($data['associated_products'] as $associatedSku) {
                    $associatedProduct = $this->productFactory->create()->loadByAttribute('sku', $associatedSku);
                    $position++;

                    $productLink = $this->productLinkFactory->create();

                    $productLink->setSku($product->getSku())
                        ->setLinkType('associated')
                        ->setLinkedProductSku($associatedProduct->getSku())
                        ->setLinkedProductType($associatedProduct->getTypeId())
                        ->setPosition($position)
                        ->getExtensionAttributes()
                        ->setQty(0);

                    $associated[] = $productLink;
                }

                $product->setProductLinks($associated);
                $product->save();
            }
        }
    }
}
