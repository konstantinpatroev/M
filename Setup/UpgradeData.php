<?php
/**
 * Copyright © 2016 MageWorx. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace MageWorx\GiftCards\Setup;

use Magento\Catalog\Api\Data\ProductAttributeInterface;
use Magento\Eav\Model\Entity\Attribute\ScopedAttributeInterface;
use Magento\Framework\Setup\UpgradeDataInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Eav\Setup\EavSetup;
use Magento\Eav\Setup\EavSetupFactory;
use Magento\Catalog\Setup\CategorySetupFactory;

/**
 * Upgrade Data script
 * @codeCoverageIgnore
 */
class UpgradeData implements UpgradeDataInterface
{
    /**
     * Category setup factory
     *
     * @var CategorySetupFactory
     */
    protected $categorySetupFactory;

    /**
     * Init
     *
     * @param CategorySetupFactory $categorySetupFactory
     */
    public function __construct(CategorySetupFactory $categorySetupFactory)
    {
        $this->categorySetupFactory = $categorySetupFactory;
    }

    /**
     * {@inheritdoc}
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    public function upgrade(ModuleDataSetupInterface $setup, ModuleContextInterface $context)
    {
        $setup->startSetup();
        $categorySetupManager = $this->categorySetupFactory->create();

        if (version_compare($context->getVersion(), '2.1.0') < 0) {
            $categorySetupManager->removeAttribute(
                \Magento\Catalog\Model\Product::ENTITY,
                'wts_gc_type'
            );
            $categorySetupManager->removeAttribute(
                \Magento\Catalog\Model\Product::ENTITY,
                'wts_gc_additional_price'
            );
            
            $categorySetupManager->addAttribute(
                \Magento\Catalog\Model\Product::ENTITY,
                'mageworx_gc_type',
                [
                    'label'            => 'Giftcards Type',
                    'group'            => 'Product Details',
                    'required'         => true,
                    'visible_on_front' => true,
                    'apply_to'         => \MageWorx\GiftCards\Model\Product\Type\GiftCards::TYPE_CODE,
                    'input'            => 'select',
                    'source'           => 'MageWorx\GiftCards\Model\GiftCards\Source\Types',
                    ]
            );

            $categorySetupManager->addAttribute(
                \Magento\Catalog\Model\Product::ENTITY,
                'mageworx_gc_additional_price',
                [
                    'label'            => 'Predefined Prices',
                    'group'            => 'Product Details',
                    'required'         => false,
                    'visible_on_front' => true,
                    'apply_to'         => \MageWorx\GiftCards\Model\Product\Type\GiftCards::TYPE_CODE,
                    'note'             => 'List here possible gift card prices to be selected from the dropdown on the frontend. Separate them by semicolon.',
                    'sort_order'       => 35,
                    'backend'          => 'MageWorx\GiftCards\Model\GiftCards\Backend\AdditionalPrice',
                    ]
            );

            $entityTypeId = $categorySetupManager->getEntityTypeId(\Magento\Catalog\Model\Product::ENTITY);
            $defaultEntities = $categorySetupManager->getDefaultEntities();
        
            foreach ($defaultEntities['catalog_product']['attributes'] as $code => $attribute) {
                $applyTo = explode(',', $categorySetupManager->getAttribute($entityTypeId, $code, 'apply_to'));
                if (!in_array(\MageWorx\GiftCards\Model\Product\Type\GiftCards::TYPE_CODE, $applyTo) && in_array('simple', $applyTo)) {
                    $applyTo[] = \MageWorx\GiftCards\Model\Product\Type\GiftCards::TYPE_CODE;
                    $categorySetupManager->updateAttribute($entityTypeId, $code, 'apply_to', implode(',', $applyTo));
                }
            }
        }

        if (version_compare($context->getVersion(), '2.1.1', '<')) {
            $categorySetupManager->addAttribute(
                \Magento\Catalog\Model\Product::ENTITY,
                'mageworx_gc_additional_price',
                [
                    'label'            => 'Predefined Prices',
                    'group'            => 'Product Details',
                    'required'         => false,
                    'visible_on_front' => false,
                    'apply_to'         => \MageWorx\GiftCards\Model\Product\Type\GiftCards::TYPE_CODE,
                    'note'             => 'List here possible gift card prices to be selected from the dropdown on the frontend. ' .
                                          'Separate them by semicolon. Predefined Prices drop-down is displayed only if the price is equal to “0”.',
                    'sort_order'       => 35,
                    'backend'          => 'MageWorx\GiftCards\Model\GiftCards\Backend\AdditionalPrice',
                    'user_defined'     => false,
                ]
            );
        }

        if (version_compare($context->getVersion(), '2.1.7') < 0) {
            $entityTypeId = $categorySetupManager->getEntityTypeId(\Magento\Catalog\Model\Product::ENTITY);
            $categorySetupManager->addAttributeGroup(
                \Magento\Catalog\Model\Product::ENTITY,
                $categorySetupManager->getAttributeSetId($entityTypeId, 'Default'),
                'Gift Card Information',
                100
            );
            $categorySetupManager->addAttribute(
                \Magento\Catalog\Model\Product::ENTITY,
                'mageworx_gc_customer_groups',
                [
                    'label' => 'Available for Customer Groups',
                    'group' => 'Gift Card Information',
                    'input' => 'multiselect',
                    'required' => false,
                    'visible_on_front' => false,
                    'apply_to' => \MageWorx\GiftCards\Model\Product\Type\GiftCards::TYPE_CODE,
                    'sort_order' => 36,
                    'type' => 'text',
                    'system' => 0,
                    'backend' => 'MageWorx\GiftCards\Model\GiftCards\Backend\CustomerGroups',
                    'source' => 'MageWorx\GiftCards\Model\GiftCards\Source\CustomerGroups',
                    'user_defined' => false,
                ]
            );
        }

        if (version_compare($context->getVersion(), '2.1.8') < 0) {
            $categorySetupManager->addAttribute(
                \Magento\Catalog\Model\Product::ENTITY,
                'mageworx_gc_lifetime_value',
                [
                'label'            => 'Expiration Period',
                'group'            => 'Gift Card Information',
                'required'         => false,
                'visible_on_front' => false,
                'apply_to'         => \MageWorx\GiftCards\Model\Product\Type\GiftCards::TYPE_CODE,
                'sort_order'       => 37,
                'user_defined'     => false,
                ]
            );


            $categorySetupManager->addAttribute(
                \Magento\Catalog\Model\Product::ENTITY,
                'mageworx_gc_additional_price',
                [
                'label'            => 'Predefined Prices',
                'group'            => 'Gift Card Information',
                'required'         => false,
                'visible_on_front' => false,
                'apply_to'         => \MageWorx\GiftCards\Model\Product\Type\GiftCards::TYPE_CODE,
                'note'             => 'List here possible gift card prices to be selected from the dropdown on the frontend. ' .
                'Separate them by semicolon. Predefined Prices drop-down is displayed only if the price is equal to “0”.',
                'sort_order'       => 35,
                'backend'          => 'MageWorx\GiftCards\Model\GiftCards\Backend\AdditionalPrice',
                'user_defined'     => false,
                ]
            );

            $categorySetupManager->addAttribute(
                \Magento\Catalog\Model\Product::ENTITY,
                'mageworx_gc_type',
                [
                'label'            => 'Giftcards Type',
                'group'            => 'Gift Card Information',
                'required'         => true,
                'visible_on_front' => true,
                'apply_to'         => \MageWorx\GiftCards\Model\Product\Type\GiftCards::TYPE_CODE,
                'input'            => 'select',
                'sort_order'       => 34,
                'source'           => 'MageWorx\GiftCards\Model\GiftCards\Source\Types',
                ]
            );
        }

        if (version_compare($context->getVersion(), '2.1.9') < 0) {
            $categorySetupManager->addAttribute(
                \Magento\Catalog\Model\Product::ENTITY,
                'mageworx_gc_allow_open_amount',
                [
                    'label'            => 'Allow Open Amount',
                    'group'            => 'Gift Card Information',
                    'required'         => false,
                    'visible_on_front' => false,
                    'type'             => 'int',
                    'input'            => 'boolean',
                    'source'           => 'Magento\Eav\Model\Entity\Attribute\Source\Boolean',
                    'apply_to'         => \MageWorx\GiftCards\Model\Product\Type\GiftCards::TYPE_CODE,
                    'sort_order'       => 38,
                    'user_defined'     => false,
                ]
            );

            $categorySetupManager->addAttribute(
                \Magento\Catalog\Model\Product::ENTITY,
                'mageworx_gc_open_amount_min',
                [
                    'label'            => 'Open Amount Min Value',
                    'group'            => 'Gift Card Information',
                    'required'         => false,
                    'visible_on_front' => false,
                    'apply_to'         => \MageWorx\GiftCards\Model\Product\Type\GiftCards::TYPE_CODE,
                    'sort_order'       => 39,
                    'user_defined'     => false,
                ]
            );

            $categorySetupManager->addAttribute(
                \Magento\Catalog\Model\Product::ENTITY,
                'mageworx_gc_open_amount_max',
                [
                    'label'            => 'Open Amount Max Value',
                    'group'            => 'Gift Card Information',
                    'required'         => false,
                    'visible_on_front' => false,
                    'apply_to'         => \MageWorx\GiftCards\Model\Product\Type\GiftCards::TYPE_CODE,
                    'sort_order'       => 40,
                    'user_defined'     => false,
                ]
            );
        }

        $setup->endSetup();
    }
}
