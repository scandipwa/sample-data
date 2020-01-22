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

use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\Filesystem;
use Magento\Framework\File\Csv as CsvProcessor;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Framework\Exception\FileSystemException;
use Magento\Framework\Component\ComponentRegistrar;
use Magento\Framework\Component\ComponentRegistrarInterface;

class FileParser
{
    /**
     * Path to the data files from root magento folder
     */
    const PATH_TO_DATA = 'code/ScandiPWA/SampleData/files/data/';

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
     * @var CsvProcessor
     */
    protected $csvProcessor;

    /**
     * @var DirectoryList
     */
    protected $directoryList;

    /**
     * @var StoreManagerInterface
     */
    private $storeManager;

    /**
     * @var string
     */
    private $sourcePath;

    /**
     * @var String
     */
    private $pathToData;

    /**
     * FileParser constructor.
     *
     * @param Filesystem $fileSystem
     * @param CsvProcessor $csvProcessor
     * @param DirectoryList $directoryList
     * @param StoreManagerInterface $storeManager
     * @param ComponentRegistrarInterface $registrar
     */
    public function __construct(
        Filesystem $fileSystem,
        CsvProcessor $csvProcessor,
        DirectoryList $directoryList,
        StoreManagerInterface $storeManager,
        ComponentRegistrarInterface $registrar
    ) {
        $this->rootDirectory = $fileSystem->getDirectoryRead(DirectoryList::APP);
        $this->csvProcessor = $csvProcessor;
        $this->directoryList = $directoryList;
        $this->storeManager = $storeManager;
        $this->pathToData = $registrar->getPath(ComponentRegistrar::MODULE, self::MIGRATION_MODULE) . '/files/data/';
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
                '%shtml/%s',
                $this->pathToData,
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
                '%sjson/%s',
                $this->pathToData,
                $filePath
            )
        );

        return json_decode($data, true);
    }

    /**
     * Get content of csv file
     *
     * @param $filePath
     *
     * @return array
     * @throws \Exception
     */
    public function getCsvContent($filePath)
    {
        return $this->csvProcessor->getData(
            sprintf(
                '%s/%scsv/%s',
                $this->directoryList->getPath(DirectoryList::APP),
                $this->pathToData,
                $filePath
            )
        );
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
