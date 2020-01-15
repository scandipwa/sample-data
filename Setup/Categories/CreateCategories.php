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

class CreateCategories
{
    const PATH = 'categories/categories.json';

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
     * @param FileParser $fileParser
     * @param StoreManagerInterface $storeManager
     * @param Category $category
     * @param CategoryFactory $categoryFactory
     */
    public function __construct(
        FileParser $fileParser,
        StoreManagerInterface $storeManager,
        Category $category,
        CategoryFactory $categoryFactory
    ){
        $this->fileParser = $fileParser;
        $this->storeManager = $storeManager;
        $this->category = $category;
        $this->categoryFactory = $categoryFactory;
    }

    /**
     * @inheritDoc
     */
    public function apply(SetupInterface $setup = null)
    {
        $store = $this->storeManager->getStore();
        $storeId = $store->getStoreId();
        $rootCategoryId = $store->getRootCategoryId();
        $rootCategory = $this->category->load($rootCategoryId);

        foreach ($this->fileParser->getJSONContent(self::PATH) as $data) {
            $url = strtolower($data['name']);
            $cleanUrl = trim(preg_replace('/ +/', '', preg_replace('/[^A-Za-z0-9 ]/', '', urldecode(html_entity_decode(strip_tags($url))))));
            $categoryTmp = $this->categoryFactory->create();
            $categoryTmp->setName($data['name']);
            $categoryTmp->setIsActive(true);
            $categoryTmp->setUrlKey($cleanUrl);
            $categoryTmp->setData('description', 'Demo Description');
            $categoryTmp->setParentId($rootCategory->getId());
            $categoryTmp->setStoreId($storeId);
            $categoryTmp->setPath($rootCategory->getPath());
            $categoryTmp->save();
        }
    }
}
