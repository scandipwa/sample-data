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

use Exception;
use Magento\Cms\Model\Block;
use Magento\Cms\Model\BlockFactory;
use Magento\Cms\Model\Page;
use Magento\Cms\Model\PageFactory;
use Magento\Cms\Model\ResourceModel\Block as BlockResource;
use Magento\Cms\Model\ResourceModel\Page as PageResource;
use Magento\Framework\App\State as AppState;
use Magento\Framework\Exception\LocalizedException;

class Cms
{
    const AREA_CODE = 'adminhtml';

    /**
     * @var AppState
     */
    protected $appState;

    /**
     * @var BlockFactory
     */
    protected $blockFactory;

    /**
     * @var BlockResource
     */
    protected $blockResource;

    /**
     * @var PageFactory
     */
    protected $pageFactory;

    /**
     * @var PageResource
     */
    protected $pageResource;

    /**
     * CmsHelper constructor.
     *
     * @param AppState $appState
     * @param BlockFactory $blockFactory
     * @param BlockResource $blockResource
     * @param PageFactory $pageFactory
     * @param PageResource $pageResource
     */
    public function __construct(
        AppState $appState,
        BlockFactory $blockFactory,
        BlockResource $blockResource,
        PageFactory $pageFactory,
        PageResource $pageResource
    ) {
        $this->appState = $appState;
        $this->blockFactory = $blockFactory;
        $this->blockResource = $blockResource;
        $this->pageFactory = $pageFactory;
        $this->pageResource = $pageResource;
    }


    /**
     * @param string $identifier
     * @return $this
     * @throws Exception
     */
    public function assertBlockIdentifier(string $identifier)
    {
        if (!preg_match('#^[a-z]#', $identifier) || preg_match('#[^a-z0-9-]#', $identifier)) {
            throw new LocalizedException(__('Invalid block identifier: %1', $identifier));
        }

        return $this;
    }

    /**
     * @param string $identifier
     * @return $this
     * @throws Exception
     */
    public function assertPageIdentifier(string $identifier)
    {
        if (preg_match('#^[0-9]+$#', $identifier) || preg_match('#[^a-z0-9\-]#', $identifier)) {
            throw new LocalizedException(__('Invalid page identifier: %1', $identifier));
        }

        return $this;
    }

    /**
     * @param string $identifier
     * @param string $content
     * @param array $data
     * @return Block
     * @throws Exception
     */
    public function updateBlock(string $identifier, string $content, array $data = []): Block
    {
        $this->assertBlockIdentifier($identifier);
        $storeIds = $this->fetchStoreIds($data);

        /** @var Block $cmsBlock */
        $cmsBlock = $this->blockFactory->create();
        // add store filter before load by identifier
        $cmsBlock->setStoreId($storeIds[0]);
        $this->blockResource->load($cmsBlock, $identifier, 'identifier');

        if (!$cmsBlock->getId()) {
            $data = array_merge(['is_active' => '1', 'title' => $identifier], $data);
        }

        $cmsBlock->addData($data);
        $cmsBlock->setIdentifier($identifier);
        $cmsBlock->setContent($content);

        return $this->saveBlock($cmsBlock);
    }

    /**
     * @param string $identifier
     * @param string $content
     * @param array $data
     * @return Page
     */
    public function updatePage(string $identifier, string $content, array $data = []): Page
    {
        $this->assertPageIdentifier($identifier);
        $storeIds = $this->fetchStoreIds($data);

        /** @var Page $cmsPage */
        $cmsPage = $this->pageFactory->create();
        // add store filter before load by identifier
        $cmsPage->setStoreId($storeIds[0]);
        $this->pageResource->load($cmsPage, $identifier, 'identifier');

        if (!$cmsPage->getId()) {
            $data = array_merge(['title' => $identifier], $data);
        }

        $cmsPage->addData($data);
        $cmsPage->setIdentifier($identifier);
        $cmsPage->setContent($content);

        return $this->savePage($cmsPage);
    }

    /**
     * @param array $data
     * @return array
     * @throws LocalizedException
     */
    protected function fetchStoreIds(array $data): array
    {
        $storeIds = isset($data['stores']) ? (array)$data['stores'] : [];
        if (empty($storeIds)) {
            throw new LocalizedException(__('Store id is missing'));
        }

        return $storeIds;
    }

    /**
     * @param Block $cmsBlock
     * @return Block
     */
    protected function saveBlock(Block $cmsBlock): Block
    {
        $this->appState->emulateAreaCode(
            self::AREA_CODE,
            [$this->blockResource, 'save'],
            ['cms_block' => $cmsBlock]
        );

        return $cmsBlock;
    }

    /**
     * @param Page $cmsPage
     * @return Page
     */
    protected function savePage(Page $cmsPage): Page
    {
        $this->appState->emulateAreaCode(
            self::AREA_CODE,
            [$this->pageResource, 'save'],
            ['cms_page' => $cmsPage]
        );

        return $cmsPage;
    }
}