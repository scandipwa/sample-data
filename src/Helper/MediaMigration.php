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

use Magento\Framework\Module\Dir\Reader as ModuleReader;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\Filesystem;
use Magento\Framework\Module\Dir;

class MediaMigration
{
    /**
     * @var ModuleReader
     */
    protected $moduleReader;

    /**
     * @var Filesystem
     */
    protected $fileSystem;

    /**
     * @param ModuleReader $moduleReader
     * @param Filesystem $fileSystem
     */
    public function __construct(
        ModuleReader $moduleReader,
        Filesystem $fileSystem
    ){
        $this->fileSystem = $fileSystem;
        $this->moduleReader = $moduleReader;
    }

    /**
     * Copies an array of files from a source to a destination media directory.
     *
     * @param $files
     * @param string $sourceModule Scandiweb_Migration
     * @param null $folderPath
     */
    public function copyMediaFiles($files, $sourceModule, $folderPath = null)
    {
        $sourcePath = $this->_getSourceMediaDirectory($sourceModule);
        $destinationPath = $this->_getDestinationMediaDirectory($folderPath);

        $rootDirectory = $this->fileSystem->getDirectoryWrite(DirectoryList::ROOT);

        $relativeSourcePath = str_replace($rootDirectory->getAbsolutePath(), '', $sourcePath);
        $relativeDestinationPath = str_replace($rootDirectory->getAbsolutePath(), '', $destinationPath);

        foreach ($files as $file) {
            if ($rootDirectory->isFile($relativeSourcePath . $file)) {
                $rootDirectory->copyFile($relativeSourcePath . $file, $relativeDestinationPath . $file);
            }
        }
    }

    /**
     * Gets the directory from which media files are copied.
     *
     * @param $sourceModule
     * @return string
     */
    protected function _getSourceMediaDirectory($sourceModule)
    {
        return $this->moduleReader->getModuleDir(
                Dir::MODULE_VIEW_DIR, $sourceModule
            ). DIRECTORY_SEPARATOR . 'media' . DIRECTORY_SEPARATOR;
    }

    /**
     * Gets the directory in which media files are copied to.
     *
     * @param string $folderPath
     * @return string
     */
    protected function _getDestinationMediaDirectory($folderPath = 'wysiwyg')
    {
        if (!$folderPath) {
            return $this->fileSystem->getDirectoryRead(DirectoryList::MEDIA)->getAbsolutePath();
        }

        return $this->fileSystem->getDirectoryWrite(DirectoryList::MEDIA)
                ->getAbsolutePath() . $folderPath . DIRECTORY_SEPARATOR;
    }
}