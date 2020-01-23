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

class AddCmsPages
{
    const PATH = 'cms-pages/cms-pages.json';
    const PAGE_LAYOUT = '1column';

    /**
     * @var FileParser
     */
    private $fileParser;

    /**
     * @var Cms
     */
    private $cmsHelper;

    /**
     * AddAboutUsPage constructor.
     *
     * @param FileParser $fileParser
     * @param Cms $cms
     */
    public function __construct(
        FileParser $fileParser,
        Cms $cms
    )
    {
        $this->fileParser = $fileParser;
        $this->cmsHelper = $cms;
    }

    /**
     * Applies migration.
     *
     * @param SetupInterface $setup
     */
    public function apply(SetupInterface $setup = null)
    {
        foreach ($this->fileParser->getCMSBlockDataFromJson(self::PATH) as $data) {
            $this->cmsHelper->updatePage(
                $data['identifier'],
                $data['content'],
                [
                    'stores' => [$data['stores']],
                    'title' => $data['title'],
                    'content_heading' => $data['content_heading'],
                    'page_width' => $data['page_width'],
                    'page_layout' => self::PAGE_LAYOUT
                ]
            );
        }
    }


}