<?php
/**
 * Copyright © 2017 MageWorx. All rights reserved.
 * See LICENSE.txt for license details.
 */
namespace MageWorx\GiftCards\Observer;

use Magento\Framework\Event\ObserverInterface;
use MageWorx\GiftCards\Model\GiftCards;

class SalesModelServiceQuoteSubmitBeforeObserver implements ObserverInterface
{
    protected $giftCardsSession;
    protected $giftCardsCollection;
    protected $giftCardsOrderCollection;
    protected $giftCardsRepository;
    protected $giftCardsOrderRepository;
    protected $giftCardsFactory;
    protected $giftCardsOrderFactory;
    protected $helper;
    protected $logger;

    public function __construct(
        \MageWorx\GiftCards\Model\Session $giftcardsSession,
        \MageWorx\GiftCards\Model\ResourceModel\GiftCards\CollectionFactory $giftcardsCollection,
        \MageWorx\GiftCards\Model\ResourceModel\Order\CollectionFactory $giftcardsOrderCollection,
        \MageWorx\GiftCards\Model\GiftCardsRepository $giftCardsRepository,
        \MageWorx\GiftCards\Model\OrderRepository $giftCardsOrderRepository,
        \MageWorx\GiftCards\Model\GiftCardsFactory $giftCardsFactory,
        \MageWorx\GiftCards\Model\OrderFactory $giftCardsOrderFactory,
        \MageWorx\GiftCards\Helper\Data $helper,
        \Psr\Log\LoggerInterface $logger
    ) {
        $this->giftCardsSession = $giftcardsSession;
        $this->giftCardsCollection = $giftcardsCollection;
        $this->giftCardsOrderCollection = $giftcardsOrderCollection;
        $this->giftCardsRepository = $giftCardsRepository;
        $this->giftCardsOrderRepository = $giftCardsOrderRepository;
        $this->giftCardsFactory = $giftCardsFactory;
        $this->giftCardsOrderFactory = $giftCardsOrderFactory;
        $this->helper = $helper;
        $this->logger = $logger;
    }

    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        $order = $observer->getOrder();

        if (!$order && $observer->getOrders()) {
            $orders = $observer->getOrders();
            $order = $orders[0];
        }

        $quote = $observer->getQuote();

        if ((bool)$this->giftCardsSession->getActive() === true && $giftCardsIds = $this->giftCardsSession->getGiftCardsIds()) {
            $order->setMageworxGiftcardsAmount($quote->getMageworxGiftcardsAmount());
            $order->setBaseMageworxGiftcardsAmount($quote->getBaseMageworxGiftcardsAmount());
            $order->setMageworxGiftcardsDescription($quote->getMageworxGiftcardsDescription());
        }

        return $this;
    }
}
