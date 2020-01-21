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
use Magento\Store\Model\StoreManagerInterface;
use Magento\Catalog\Model\CategoryFactory;
use ScandiPWA\SampleData\Helper\FileParser;
use ScandiPWA\MenuOrganizer\Model\MenuFactory;
use ScandiPWA\MenuOrganizer\Model\ItemFactory;
use ScandiPWA\SampleData\Helper\MediaMigration;

class CreateMenu
{
    const PATH = 'menu/menu.json';
    const MIGRATION_MODULE = 'ScandiPWA_SampleData';

    /**
     * @var FileParser
     */
    private $fileParser;

    /**
     * @var MenuFactory
     */
    private $menuFactory;

    /**
     * @var ItemFactory
     */
    private $itemFactory;

    /**
     * @var StoreManagerInterface
     */
    private $storeManager;

    /**
     * @var CategoryFactory
     */
    private $categoryFactory;

    /**
     * @var MediaMigration
     */
    protected $mediaMigration;

    /**
     * @param FileParser $fileParser
     * @param MenuFactory $menuFactory
     * @param ItemFactory $itemFactory
     * @param StoreManagerInterface $storeManager
     * @param CategoryFactory $categoryFactory
     * @param MediaMigration $mediaMigration
     *
     */
    public function __construct(
        FileParser $fileParser,
        MenuFactory $menuFactory,
        ItemFactory $itemFactory,
        StoreManagerInterface $storeManager,
        CategoryFactory $categoryFactory,
        MediaMigration $mediaMigration
    )
    {
        $this->fileParser = $fileParser;
        $this->menuFactory = $menuFactory;
        $this->itemFactory = $itemFactory;
        $this->storeManager = $storeManager;
        $this->categoryFactory = $categoryFactory;
        $this->mediaMigration = $mediaMigration;
    }

    /**
     * Applies migration.
     *
     * @param SetupInterface $setup
     */
    public function apply(SetupInterface $setup = null)
    {
        $store = $this->storeManager->getStore();
        $rootCategoryId = $store->getRootCategoryId();
        $this->copyImages();

        foreach ($this->fileParser->getJSONContent(self::PATH) as $menu) {

            $newMenu = $this->menuFactory
                ->create()
                ->load($menu['identifier'], 'identifier');

            if ($newMenu->getIdentifier()) {
                continue;
            }

            $newMenu->setIdentifier($menu['identifier'])
                ->setTitle($menu['title'])
                ->setMenuIsActive($menu['menu_is_active'])
                ->setStores([0])
                ->save();

            foreach ($menu['items'] as $menuItem) {

                if (isset($menuItem['url_key'])) {
                    $category = $this->categoryFactory->create()->loadByAttribute('url_key', $menuItem['url_key']);

                    if ($category === false) {
                        continue;
                    }

                    $categoryId = $category->getId();
                } else {
                    $categoryId = $rootCategoryId;
                }

                $this->itemFactory->create()
                    ->addData($menuItem)
                    ->setCategoryId($categoryId)
                    ->setParentId($menuItem['parent_id'])
                    ->setMenuId($newMenu->getId())
                    ->save();
            }
        }
    }

    /**
     * Adds About us page images to wysiwyg folder
     * @return void
     */
    private function copyImages()
    {
        $media = [
            'man.png',
            'woman.png'
        ];

        $this->mediaMigration->copyMediaFiles($media, self::MIGRATION_MODULE, 'scandipwa_menuorganizer_item_icons');
    }

}
