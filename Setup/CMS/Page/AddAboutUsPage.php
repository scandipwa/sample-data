<?php
/**
 * ScandiPWA_SampleData
 *
 * @category    Scandiweb
 * @package     ScandiPWA_SampleData
 * @author      Vadims Petrovs <info@scandiweb.com>
 * @copyright   Copyright (c) 2020 Scandiweb, Ltd (https://scandiweb.com)
 */

namespace ScandiPWA\SampleData\Setup\CMS\Page;

use ScandiPWA\SampleData\Helper\FileParser;
use Magento\Framework\Setup\SetupInterface;
use ScandiPWA\SampleData\Helper\Cms;
use ScandiPWA\SampleData\Helper\MediaMigration;

class AddAboutUsPage
{
    const PATH = 'cms-pages/about-us.json';
    const PAGE_LAYOUT = '1column';
    const MIGRATION_MODULE = 'ScandiPWA_SampleData';

    /**
     * @var FileParser
     */
    private $fileParser;

    /**
     * @var MediaMigration
     */
    protected $mediaMigration;

    /**
     * @var Cms
     */
    private $cmsHelper;

    /**
     * AddAboutUsPage constructor.
     *
     * @param FileParser $fileParser
     * @param MediaMigration $mediaMigration
     * @param Cms $cms
     */
    public function __construct(
        FileParser $fileParser,
        MediaMigration $mediaMigration,
        Cms $cms
    )
    {
        $this->fileParser = $fileParser;
        $this->mediaMigration = $mediaMigration;
        $this->cmsHelper = $cms;
    }

    /**
     * @inheritDoc
     */
    public function apply(SetupInterface $setup = null)
    {
        $this->copyImages();

        foreach ($this->fileParser->getCMSBlockDataFromJson(self::PATH) as $data) {
            $this->cmsHelper->createPage(
                $data['identifier'],
                $data['content'],
                [
                    'stores' => [$data['stores']],
                    'title' => $data['title'],
                    'page_layout' => self::PAGE_LAYOUT
                ]
            );
        }
    }

    /**
     * Adds About us page images to wysiwyg folder
     * @return void
     */
    private function copyImages()
    {
        $media = [
            'cms/about-us/hero.png',
            'cms/about-us/history.jpg',
            'cms/about-us/quality-products.jpg',
            'cms/about-us/customer-service.jpg',
            'cms/about-us/vaper.png'
        ];

        $this->mediaMigration->copyMediaFiles($media, self::MIGRATION_MODULE, 'wysiwyg');
    }
}