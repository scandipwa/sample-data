<?php

/**
 * ScandiPWA_SampleData
 *
 * @category    Scandiweb
 * @package     ScandiPWA_SampleData
 * @author      Vadims Petrovs <info@scandiweb.com>
 * @copyright   Copyright (c) 2020 Scandiweb, Ltd (https://scandiweb.com)
 */

namespace ScandiPWA\SampleData\Setup\Categories;

use Magento\Framework\Setup\SetupInterface;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Framework\UrlInterface;
use Magento\Catalog\Model\Category;
use Magento\Catalog\Model\CategoryFactory;
use ScandiPWA\SampleData\Helper\FileParser;
use ScandiPWA\SampleData\Helper\MediaMigration;

class CreateCategories
{
    const PATH = 'categories/categories.json';
    const MIGRATION_MODULE = 'ScandiPWA_SampleData';

    /**
     * @var FileParser
     */
    private $fileParser;

    /**
     * @var StoreManagerInterface
     */
    private $storeManager;

    /**
     * @var Category
     */
    private $category;

    /**
     * @var CategoryFactory
     */
    private $categoryFactory;

    /**
     * @var MediaMigration
     */
    private $mediaHelper;

    /**
     * @param FileParser $fileParser
     * @param StoreManagerInterface $storeManager
     * @param Category $category
     * @param CategoryFactory $categoryFactory
     * @param MediaMigration $mediaHelper
     */
    public function __construct(
        FileParser $fileParser,
        StoreManagerInterface $storeManager,
        Category $category,
        CategoryFactory $categoryFactory,
        MediaMigration $mediaHelper
    ){
        $this->fileParser = $fileParser;
        $this->storeManager = $storeManager;
        $this->category = $category;
        $this->categoryFactory = $categoryFactory;
        $this->mediaHelper = $mediaHelper;
    }

    /**
     * Applies migration.
     *
     * @param SetupInterface $setup
     */
    public function apply(SetupInterface $setup = null)
    {
        $store = $this->storeManager->getStore();
        $storeId = $store->getStoreId();
        $rootCategoryId = $store->getRootCategoryId();
        $rootCategory = $this->category->load($rootCategoryId);

        $files = [
            'mens.jpg',
            'womens.jpg'
        ];

        $this->mediaHelper->copyMediaFiles($files,self::MIGRATION_MODULE, 'catalog/category');

        foreach ($this->fileParser->getJSONContent(self::PATH) as $data) {
            $categoryTmp = $this->categoryFactory->create();
            $url = strtolower($data['name']);
            $cleanUrl = trim(preg_replace('/ +/', '', preg_replace('/[^A-Za-z0-9 ]/', '', urldecode(html_entity_decode(strip_tags($url))))));

            if ($categoryTmp->loadByAttribute('url_key', $cleanUrl) !== false) {
                continue;
            }

            $categoryTmp->setName($data['name']);
            $categoryTmp->setIsActive(true);
            $categoryTmp->setUrlKey($cleanUrl);
            $categoryTmp->setDescription($data['description']);
            $categoryTmp->setParentId($rootCategory->getId());
            $categoryTmp->setStoreId($storeId);
            $mediaAttribute =['image', 'small_image', 'thumbnail'];
            $categoryTmp->setImage($data['image'], $mediaAttribute, true, false);
            $categoryTmp->setPath($rootCategory->getPath());
            $categoryTmp->setDisplayMode('PRODUCTS_AND_PAGE');
            $categoryTmp->save();
        }
    }
}
