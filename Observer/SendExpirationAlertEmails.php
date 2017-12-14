<?php
/**
 * Copyright © 2017 MageWorx. All rights reserved.
 * See LICENSE.txt for license details.
 */
namespace MageWorx\GiftCards\Observer;

use Magento\Framework\Event\ObserverInterface;

class SendExpirationAlertEmails implements ObserverInterface
{
    /**
     * @var \MageWorx\GiftCards\Model\ResourceModel\GiftCards\CollectionFactory
     */
    protected $giftcardsCollection;

    /**
     * @var \MageWorx\GiftCards\Model\GiftCardsFactory
     */
    protected $giftCardsFactory;

    /**
     * @var \MageWorx\GiftCards\Helper\Data
     */
    protected $helper;

    /**
     * SendExpirationAlertEmails constructor.
     * @param \MageWorx\GiftCards\Model\ResourceModel\GiftCards\CollectionFactory $giftcardsCollection
     * @param \MageWorx\GiftCards\Model\GiftCardsFactory $giftCardsFactory
     * @param \MageWorx\GiftCards\Helper\Data $helper
     */
    public function __construct(
        \MageWorx\GiftCards\Model\ResourceModel\GiftCards\CollectionFactory $giftcardsCollection,
        \MageWorx\GiftCards\Model\GiftCardsFactory $giftCardsFactory,
        \MageWorx\GiftCards\Helper\Data $helper
    ) {
        $this->giftcardsCollection = $giftcardsCollection;
        $this->giftCardsFactory = $giftCardsFactory;
        $this->helper = $helper;
    }

    /**
     * @param \Magento\Framework\Event\Observer $observer
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        $cards = $this->giftcardsCollection->create()
            ->addFieldToFilter(
                'expiration_alert_email_send',
                0
            )
            ->load();

        foreach ($cards as $card) {
            $model = $this->giftCardsFactory->create();
            $model->load($card->getId());

            if ($this->helper->getAlertDays() && $model->getExpireDate()) {
                if ($this->helper->calculateExpireIn($model->getExpireDate()) <= $this->helper->getAlertDays()) {
                    $model->sendCardExpirationAlert();
                    $model->setExpirationAlertEmailSend(true);
                    $model->save();
                }
            }
        }
    }
}
