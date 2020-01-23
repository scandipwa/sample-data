<?php

/**
 * ScandiPWA_SampleData
 *
 * @category    Scandiweb
 * @package     ScandiPWA_SampleData
 * @author      Vadims Petrovs <info@scandiweb.com>
 * @copyright   Copyright (c) 2020 Scandiweb, Ltd (https://scandiweb.com)
 */

namespace ScandiPWA\SampleData\Helper;

use Magento\Framework\Filesystem;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Framework\Exception\FileSystemException;
use Magento\Framework\Component\ComponentRegistrar;
use Magento\Framework\Component\ComponentRegistrarInterface;

class FileParser
{
    /**
     * Migration module name
     */
    const MIGRATION_MODULE = 'ScandiPWA_SampleData';

    /**
     * Content array key
     */
    const CONTENT = 'content';

    /**
     * Stores array key
     */
    const STORES = 'stores';

    /**
     * @var \Magento\Framework\Filesystem\Directory\ReadInterface
     */
    protected $rootDirectory;

    /**
     * @var StoreManagerInterface
     */
    private $storeManager;

    /**
     * FileParser constructor.
     *
     * @param Filesystem $fileSystem
     * @param StoreManagerInterface $storeManager
     * @param ComponentRegistrarInterface $registrar
     */
    public function __construct(
        Filesystem $fileSystem,
        StoreManagerInterface $storeManager,
        ComponentRegistrarInterface $registrar
    ) {
        $this->storeManager = $storeManager;
        $pathToData = $registrar->getPath(ComponentRegistrar::MODULE, self::MIGRATION_MODULE) . '/files/data/';
        $this->rootDirectory = $fileSystem->getDirectoryReadByPath($pathToData);
    }

    /**
     * Get content from html file
     *
     * @param string $filePath Relative path to the html file from data/html folder
     *
     * @return string
     * @throws \Magento\Framework\Exception\FileSystemException
     */
    public function getHtmlContent($filePath)
    {
        return $this->rootDirectory->readFile(
            sprintf(
                'html/%s',
                $filePath
            )
        );
    }

    /**
     * Return content of json file as associative array
     *
     * @param string $filePath Relative path to the json file from data/json folder
     *
     * @return array
     * @throws \Magento\Framework\Exception\FileSystemException
     */
    public function getJSONContent($filePath)
    {
        $data = $this->rootDirectory->readFile(
            sprintf(
                'json/%s',
                $filePath
            )
        );

        return json_decode($data, true);
    }
    /**
     * Return CMS Block Data From Json
     *
     * @param $path
     *
     * @return array
     * @throws FileSystemException
     */
    public function getCMSBlockDataFromJson($path)
    {
        $data = $this->getJSONContent($path);

        foreach ($data as &$cmsBlockData) {
            $blockContent = $this->getHtmlContent($cmsBlockData[self::CONTENT]);
            if (!empty($blockContent)) {
                $cmsBlockData[self::CONTENT] = $blockContent;
            }

            if (!empty($cmsBlockData[self::STORES])) {
                $cmsBlockData[self::STORES] = $this->getStoreId($cmsBlockData[self::STORES]);
            } else {
                $cmsBlockData[self::STORES] = 0;
            }
        }

        return $data;
    }

    /**
     * Gets store Ids
     *
     * @param $storeCode
     * @return int
     */
    private function getStoreId($storeCode)
    {
        return $this->storeManager->getStore($storeCode)->getId();
    }
}
