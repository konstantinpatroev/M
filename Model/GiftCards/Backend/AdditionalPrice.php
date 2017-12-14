<?php
/**
 * Copyright © 2016 MageWorx. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace MageWorx\GiftCards\Model\GiftCards\Backend;

class AdditionalPrice extends \Magento\Eav\Model\Entity\Attribute\Backend\AbstractBackend
{
    public function validate($object)
    {
        $attrCode = $this->getAttribute()->getAttributeCode();
        $value = $object->getData($attrCode);
        if ($value) {
            foreach ($value as $price) {
                if (!preg_match('/(^\d+(\.{0,1}\d{0,})+$)|^$/', $price['mageworx_gc_additional_price'])) {
                    throw new \Magento\Framework\Exception\LocalizedException(
                        __('Not correct value for Gift Card Amount. Examples: 100, 200.33, 300.56')
                    );
                }
            }
        }

        return true;
    }
}
