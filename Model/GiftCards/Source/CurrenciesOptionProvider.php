<?php
/**
 * Copyright © 2016 MageWorx. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace MageWorx\GiftCards\Model\GiftCards\Source;

class CurrenciesOptionProvider implements \Magento\Framework\Data\OptionSourceInterface
{
    const DEFAULT_CURRENCY_PLACEHOLDER = 'Default Currency';
    protected $storeManagerInterface;

    public function __construct(
        \Magento\Store\Model\StoreManagerInterface $storeManagerInterface
    ) {
        $this->storeManagerInterface = $storeManagerInterface;
    }

    public function getAllOptions()
    {
        $result = [];
        foreach ($this->storeManagerInterface->getStores(true) as $nextStore) {
            foreach ($nextStore->getAvailableCurrencyCodes() as $nextCurrencyCode) {
                $result[$nextCurrencyCode] = $nextCurrencyCode;
            }
        }
        array_unshift($result, __(self::DEFAULT_CURRENCY_PLACEHOLDER));
        return $result;
    }

    /**
     * {@inheritdoc}
     */
    public function toOptionArray()
    {
        $types = [];
        foreach ($this->getAllOptions() as $value => $label) {
            $types[] = ['label' => $label, 'value' => $value];
        }
        return $types;
    }
}
