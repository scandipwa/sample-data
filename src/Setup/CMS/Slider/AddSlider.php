<?php

/**
 * ScandiPWA_SampleData
 *
 * @category    Scandiweb
 * @package     ScandiPWA_SampleData
 * @author      Vadims Petrovs <info@scandiweb.com>
 * @copyright   Copyright (c) 2020 Scandiweb, Ltd (https://scandiweb.com)
 */

namespace ScandiPWA\SampleData\Setup\CMS\Slider;

use Magento\Framework\Setup\SetupInterface;
use ScandiPWA\SampleData\Helper\FileParser;
use ScandiPWA\SampleData\Helper\MediaMigration;
use Scandiweb\Slider\Model\SliderFactory;
use Scandiweb\Slider\Model\SlideFactory;

class AddSlider
{
    const PATH = 'sliders/homepage-slider.json';
    const MIGRATION_MODULE = 'ScandiPWA_SampleData';

    /**
     * @var FileParser
     */
    private $fileParser;

    /**
     * @var SliderFactory
     */
    private $sliderFactory;

    /**
     * @var SlideFactory
     */
    private $slideFactory;

    /**
     * @var MediaMigration
     */
    protected $mediaMigration;

    /**
     * @param FileParser $fileParser
     * @param SliderFactory $sliderFactory
     * @param SlideFactory $slideFactory
     * @param MediaMigration $mediaMigration
     *
     */
    public function __construct(
        FileParser $fileParser,
        SliderFactory $sliderFactory,
        SlideFactory $slideFactory,
        MediaMigration $mediaMigration
    )
    {
        $this->fileParser = $fileParser;
        $this->sliderFactory = $sliderFactory;
        $this->slideFactory = $slideFactory;
        $this->mediaMigration = $mediaMigration;
    }

    /**
     * Applies migration.
     *
     * @param SetupInterface $setup
     */
    public function apply(SetupInterface $setup = null)
    {
        $this->copyImages();

        foreach ($this->fileParser->getJSONContent(self::PATH) as $slider) {

            $newSlider = $this->sliderFactory
                ->create()
                ->load($slider['title'], 'title');

            if ($newSlider->getTitle()) {
                continue;
            }

            $newSlider->addData($slider)->save();
            $sliderId = $newSlider->getSliderId();

            foreach ($slider['slides'] as $slideData) {
                $this->slideFactory->create()
                    ->addData($slideData)
                    ->setSliderId($sliderId)
                    ->setStores([0])
                    ->setSlideText($this->fileParser->getHtmlContent($slideData['content']))
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
            'slider-woman-on-the-beach.jpg'
        ];

        $this->mediaMigration->copyMediaFiles($media, self::MIGRATION_MODULE, 'scandiweb/slider/s/l');
    }
}
