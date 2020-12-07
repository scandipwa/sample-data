<?php

/**
 * ScandiPWA_SampleData
 *
 * @category    Scandiweb
 * @package     ScandiPWA_SampleData
 * @author      Vadims Petrovs <info@scandiweb.com>
 * @copyright   Copyright (c) 2020 Scandiweb, Ltd (https://scandiweb.com)
 */

namespace ScandiPWA\SampleData\Setup\CMS\Block;

use ScandiPWA\SampleData\Helper\FileParser;
use Magento\Framework\Setup\SetupInterface;
use ScandiPWA\SampleData\Helper\Cms;
use ScandiPWA\SampleData\Helper\MediaMigration;

class AddCmsBlocks
{
    const PATH = 'cms-blocks/cms-blocks.json';
    const MIGRATION_MODULE = 'ScandiPWA_SampleData';

    /**
     * @var Cms
     */
    private $cmsHelper;

    /**
     * @var MediaMigration
     */
    protected $mediaMigration;

    /**
     * @var FileParser
     */
    private $fileParser;

    /**
     * @param Cms $cmsHelper
     * @param MediaMigration $mediaMigration
     * @param FileParser $fileParser
     */
    public function __construct(
        Cms $cmsHelper,
        MediaMigration $mediaMigration,
        FileParser $fileParser
    ){
        $this->cmsHelper = $cmsHelper;
        $this->mediaMigration = $mediaMigration;
        $this->fileParser = $fileParser;
    }

    /**
     * Applies migration.
     *
     * @param SetupInterface $setup
     */
    public function apply(SetupInterface $setup = null)
    {
        $this->copyImages();

        foreach ($this->fileParser->getCMSBlockDataFromJson(self::PATH) as $data) {
            $this->cmsHelper->updateBlock($data['identifier'], $data['content'], $data);
        }
    }

    /**
     * Adds About us page images to wysiwyg folder
     * @return void
     */
    private function copyImages()
    {
        $homepageMedia = [
            'two-woman-in-field.jpg',
            'man-on-the-roof.jpg',
            'sunglasses-in-hands.jpg'
        ];

        $this->mediaMigration->copyMediaFiles($homepageMedia, self::MIGRATION_MODULE, 'wysiwyg/homepage');

        $socialLinksMedia = [
            'instagram.svg',
            'facebook.svg',
            'twitter.svg',
            'linkedin.png',
            'twitter.png',
            'youtube.png'
        ];

        $this->mediaMigration->copyMediaFiles($socialLinksMedia, self::MIGRATION_MODULE, 'wysiwyg/social');

        $logo = [
            'logo.png'
        ];

        $this->mediaMigration->copyMediaFiles($logo, self::MIGRATION_MODULE, 'logo/default');
    }
}
