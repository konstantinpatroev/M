<?php
namespace MageWorx\GiftCards\Ui\DataProvider\Product\Form;

use Magento\Catalog\Ui\DataProvider\Product\Form\Modifier\AbstractModifier;
use Magento\Catalog\Model\Locator\LocatorInterface;
use Magento\Directory\Helper\Data;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Customer\Api\GroupManagementInterface;
use Magento\Customer\Api\GroupRepositoryInterface;
use Magento\Ui\Component\Container;
use Magento\Ui\Component\Form\Element\DataType\Number;
use Magento\Ui\Component\Form\Element\DataType\Text;
use Magento\Ui\Component\Form\Element\Input;
use Magento\Ui\Component\Form\Field;
use Magento\Framework\Stdlib\ArrayManager;

class AdditionalPriceModifier extends AbstractModifier
{

    /**
     * @var LocatorInterface
     */
    protected $locator;

    /**
     * @param LocatorInterface $locator
     * @param ArrayManager $arrayManager
     */
    public function __construct(LocatorInterface $locator, ArrayManager $arrayManager)
    {
        $this->locator = $locator;
    }

    public function modifyMeta(array $meta)
    {
        if (isset($meta['gift-card-information'])) {
            $meta['gift-card-information']['children']['container_mageworx_gc_additional_price']['children']
            ['mageworx_gc_additional_price']['arguments']['data']['config'] = [
                'componentType' => 'dynamicRows',
                'label' => __('Amounts'),
                'renderDefaultRecord' => false,
                'recordTemplate' => 'record',
                'dataScope' => '',
                'dndConfig' => [
                    'enabled' => false,
                ],
                'disabled' => false,
            ];
            $meta['gift-card-information']['children']['container_mageworx_gc_additional_price']['children']
            ['mageworx_gc_additional_price']['children'] = [
                 'record' => [
                     'arguments' => [
                         'data' => [
                             'config' => [
                                 'componentType' => Container::NAME,
                                 'isTemplate' => true,
                                 'is_collection' => true,
                                 'component' => 'Magento_Ui/js/dynamic-rows/record',
                                 'dataScope' => '',
                                 'notice' => 'List here possible gift card prices to be selected from the dropdown on
                                 the frontend. Amounts drop-down is displayed only if the price is equal to “0”.',
                             ],
                         ],
                     ],
                     'children' => [
                         'mageworx_gc_additional_price' => [
                             'arguments' => [
                                 'data' => [
                                     'config' => [
                                         'formElement' => Input::NAME,
                                         'componentType' => Field::NAME,
                                         'dataType' => Number::NAME,
                                         'label' => __('Amount'),
                                         'dataScope' => 'mageworx_gc_additional_price',
                                     ],
                                 ],
                             ],
                         ],
                         'actionDelete' => [
                             'arguments' => [
                                 'data' => [
                                     'config' => [
                                         'componentType' => 'actionDelete',
                                         'dataType' => Text::NAME,
                                         'label' => '',
                                     ],
                                 ],
                             ],
                         ],
                     ],
                 ],
            ];
        }

        return $meta;
    }

    /**
     * {@inheritdoc}
     */
    public function modifyData(array $data)
    {
        $model = $this->locator->getProduct();
        if (isset($data[$model->getId()]['product']['mageworx_gc_additional_price'])) {
            $additionalPrice = $data[$model->getId()]['product']['mageworx_gc_additional_price'];
            $additionalPrice =  explode(';', $additionalPrice);

            if ($additionalPrice) {
                $modifiedAdditionalPrice = [];
                foreach ($additionalPrice as $price) {
                    if ($price) {
                        array_push($modifiedAdditionalPrice, ['mageworx_gc_additional_price' => $price]);
                    }
                }
                $data[$model->getId()]['product']['mageworx_gc_additional_price'] = $modifiedAdditionalPrice;
            }
        }

        return $data;
    }
}
