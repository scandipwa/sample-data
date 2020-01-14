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

class AddBottomFooterLinks
{
    const PATH = 'cms-blocks/footer/footer-links.json';

    /**
     * @var Cms
     */
    private $cmsHelper;

    /**
     * @var FileParser
     */
    private $fileParser;

    /**
     * @param Cms $cmsHelper
     * @param FileParser $fileParser
     */
    public function __construct(
        Cms $cmsHelper,
        FileParser $fileParser
    ){
        $this->cmsHelper = $cmsHelper;
        $this->fileParser = $fileParser;
    }

    /**
     * @inheritDoc
     */
    public function apply(SetupInterface $setup = null)
    {
        foreach ($this->fileParser->getCMSBlockDataFromJson(self::PATH) as $data) {
            $this->cmsHelper->createBlock($data['identifier'], $data['content'], $data);
        }
    }
}
