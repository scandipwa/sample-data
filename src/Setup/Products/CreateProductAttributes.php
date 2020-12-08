<?php

/**
 * ScandiPWA_SampleData
 *
 * @category    Scandiweb
 * @package     ScandiPWA_SampleData
 * @author      Vadims Petrovs <info@scandiweb.com>
 * @copyright   Copyright (c) 2020 Scandiweb, Ltd (https://scandiweb.com)
 */

namespace ScandiPWA\SampleData\Setup\Products;

use Magento\Framework\Setup\SetupInterface;
use ScandiPWA\SampleData\Helper\FileParser;
use Magento\Eav\Setup\EavSetupFactory;
use Magento\Catalog\Model\Product\Type;
use Magento\Catalog\Model\ResourceModel\Eav\Attribute;
use Magento\Eav\Model\Config;
use Magento\Eav\Model\ResourceModel\Entity\Attribute\Option\CollectionFactory as EavOptionCollectionFactory;
use Magento\Catalog\Model\Config as CatalogConfig;
use Magento\Eav\Api\AttributeManagementInterface;

class CreateProductAttributes
{
    const PATH = 'products/attributes.json';

    /**
     * @var FileParser
     */
    private $fileParser;

    /**
     * @var EavSetupFactory
     */
    private $eavSetupFactory;

    /**
     * @var Config
     */
    private $eavConfig;

    /**
     * @var Array
     */
    private $savedOptions;

    /**
     * @var EavOptionCollectionFactory
     */
    private $attrOptionCollectionFactory;

    /**
     * @var CatalogConfig
     */
    private $catalogConfig;

    /**
     * @var AttributeManagementInterface
     */
    private $attributeManagement;

    /**
     * @param FileParser $fileParser
     * @param EavSetupFactory $eavSetupFactory
     * @param Config $eavConfig
     * @param EavOptionCollectionFactory $attrOptionCollectionFactory
     * @param CatalogConfig $catalogConfig
     * @param AttributeManagementInterface $attributeManagement
     */
    public function __construct(
        FileParser $fileParser,
        EavSetupFactory $eavSetupFactory,
        Config $eavConfig,
        EavOptionCollectionFactory $attrOptionCollectionFactory,
        CatalogConfig $catalogConfig,
        AttributeManagementInterface $attributeManagement

    ){
        $this->fileParser = $fileParser;
        $this->eavSetupFactory = $eavSetupFactory;
        $this->eavConfig = $eavConfig;
        $this->attrOptionCollectionFactory = $attrOptionCollectionFactory;
        $this->catalogConfig = $catalogConfig;
        $this->attributeManagement = $attributeManagement;
    }

    /**
     * Applies migration.
     *
     * @param SetupInterface $setup
     */
    public function apply(SetupInterface $setup = null)
    {
        foreach ($this->fileParser->getJSONContent(self::PATH) as $attributeCode => $data) {
            if ($data) {
                $eavSetup = $this->eavSetupFactory->create(['setup' => $setup]);
                $optionValues = [];
                $swatchMap = [];

                foreach ($data['values'] as $attributeOptionKey => $value) {
                    $optionValues[] = $value['default_store_view'];
                    $swatchMap[$value['default_store_view']] = $value['swatch'];
                }

                $attribute = $this->eavConfig->getAttribute('catalog_product', $attributeCode);

                if ($attribute) {
                    $this->loadOptionCollection($attribute->getId());
                    foreach ($this->savedOptions[$attribute->getId()] as $option) {
                        if (in_array($option->getValue(), $optionValues)) {
                            $optionValues = array_diff($optionValues, [$option->getValue()]);
                        }
                    }

                    $this->savedOptions = [];
                }

                if (empty($optionValues)) {
                    continue;
                }

                $eavSetup->addAttribute(
                    \Magento\Catalog\Model\Product::ENTITY,
                    $attributeCode,
                    [
                        'type' => 'int',
                        'label' => $data['frontend_label'],
                        'global' => $data['global'],
                        'input' => 'select',
                        'required' => false,
                        'user_defined' => true,
                        'searchable' => true,
                        'filterable' => true,
                        'comparable' => true,
                        'visible_on_front' => $data['is_visible_on_front'],
                        'visible_in_advanced_search' => true,
                        'apply_to' => implode(',', [Type::TYPE_SIMPLE, Type::TYPE_VIRTUAL]),
                        'is_used_in_grid' => true,
                        'is_visible_in_grid' => false,
                        'used_in_product_listing' => $data['used_in_product_listing'] ?? false,
                        'option' => [
                            'values' => $optionValues
                        ]
                    ]
                );

                $this->eavConfig->clear();
                $attribute = $this->eavConfig->getAttribute('catalog_product', $attributeCode);

                if (!$attribute) {
                    return;
                }

                $attributeData['option'] = $this->addExistingOptions($attribute);
                $attributeData['frontend_input'] = 'select';
                $attributeData['update_product_preview_image'] = 1;
                $attributeData['use_product_image_for_swatch'] = 0;

                if ($data['frontend_input'] === 'swatch_visual') {
                    $attributeData['swatch_input_type'] = 'visual';
                    $attributeData['optionvisual'] = $this->getOptionSwatch($attributeData);
                    $attributeData['swatchvisual'] = $this->getOptionSwatchVisual($attributeData, $swatchMap);
                } else {
                    $attributeData['swatch_input_type'] = 'text';
                    $attributeData['optiontext'] = $this->getOptionSwatch($attributeData);
                    $attributeData['swatchtext'] = $this->getOptionSwatchText($attributeData, $swatchMap);
                }

                $attribute->addData($attributeData);
                $attribute->save();
                $groupId = $this->catalogConfig->getAttributeGroupId($data['attribute_set_id'], $data['attribute_group']);

                $this->attributeManagement->assign(
                    'catalog_product',
                    $data['attribute_set_id'],
                    $groupId,
                    $attributeCode,
                    999
                );
            }
        }
    }

    /**
     * Get option swatch
     *
     * @param array $attributeData
     * @return array
     */
    private function getOptionSwatch($attributeData)
    {
        $optionSwatch = ['order' => [], 'value' => [], 'delete' => []];
        $i = 0;

        foreach ($attributeData['option'] as $optionKey => $optionValue) {
            $optionSwatch['delete'][ $optionKey] = '';
            $optionSwatch['order'][$optionKey] = (string)$i++;
            $optionSwatch['value'][$optionKey] = [ $optionValue, ''];
        }

        return $optionSwatch;
    }

    /**
     * Get option swatch visual
     *
     * @param array $attributeData
     * @param array $swatchMap
     * @return array
     */
    private function getOptionSwatchVisual($attributeData, $swatchMap)
    {
        $optionSwatch = ['value' => []];
        foreach ($attributeData['option'] as $optionKey => $optionValue) {
            if (isset($swatchMap[$optionValue])) {
                $optionSwatch['value'][$optionKey] = $swatchMap[$optionValue];
            } else {
                $optionSwatch['value'][$optionKey] = $optionValue;
            }
        }

        return $optionSwatch;
    }

    /**
     * Get option swatch text
     *
     * @param array $attributeData
     * @param array $swatchMap
     * @return array
     */
    private function getOptionSwatchText($attributeData, $swatchMap)
    {
        $optionSwatch = ['value' => []];
        foreach ($attributeData['option'] as $optionKey => $optionValue) {
            if (isset($swatchMap[$optionValue])) {
                $optionSwatch['value'][$optionKey] = [$swatchMap[$optionValue]];
            } else {
                $optionSwatch['value'][$optionKey] = [$optionValue];
            }
        }

        return $optionSwatch;
    }

    /**
     * Get option swatch visual
     *
     * @param Attribute $attribute
     * @return array
     */
    private function addExistingOptions($attribute)
    {
        $options = [];
        $attributeId = $attribute->getId();

        if ($attributeId) {
            $this->loadOptionCollection($attributeId);

            foreach ($this->savedOptions[$attributeId] as $option) {
                $options[$option->getId()] = $option->getValue();
            }
        }

        return $options;
    }

    /**
     * Load options collection
     *
     * @param String $attributeId
     */
    private function loadOptionCollection($attributeId)
    {
        if (empty($this->savedOptions[$attributeId])) {
            $this->savedOptions[$attributeId] = $this->attrOptionCollectionFactory->create()
            ->setAttributeFilter($attributeId)
            ->setPositionOrder('asc', true)
            ->load();
        }
    }

}
