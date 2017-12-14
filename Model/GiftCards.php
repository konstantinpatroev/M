<?php

namespace MageWorx\GiftCards\Model;

use MageWorx\GiftCards\Api\Data\GiftCardsInterface;
use Magento\Framework\DataObject\IdentityInterface;
use Magento\Framework\Mail\Template\TransportBuilder;
use Magento\Framework\Mail\Template\FactoryInterface;
use Magento\Store\Model\StoreManagerInterface as StoreManager;

class GiftCards extends \Magento\Framework\Model\AbstractModel implements GiftCardsInterface, IdentityInterface
{
    
    const STATUS_INACTIVE    = 0;
    const STATUS_ACTIVE      = 1;
    const STATUS_USED        = 2;
    
    const STATUS_INACTIVE_LABEL    = 'Inactive';
    const STATUS_ACTIVE_LABEL      = 'Active';
    const STATUS_USED_LABEL        = 'Used';

    const TYPE_EMAIL            = 1;
    const TYPE_PRINT            = 2;
    const TYPE_OFFLINE          = 3;

    const TYPE_EMAIL_LABEL         = 'Email';
    const TYPE_PRINT_LABEL         = 'Print';
    const TYPE_OFFLINE_LABEL       = 'Offline';
    
    const CACHE_TAG         = 'mageworx_giftcards';
    
    protected $cacheTag     = 'mageworx_giftcards';
    
    protected $eventPrefix  = 'mageworx_giftcards';

    /**
     * @var TransportBuilder
     */
    protected $transportBuilder;
    
    /**
     * @var \MageWorx\GiftCards\Helper\Data
     */
    protected $helper;

    /**
     * @var \Magento\Checkout\Helper\Data
     */
    protected $checkoutHelper;

    /**
     * @var \Magento\Catalog\Model\ProductFactory
     */
    protected $productFactory;

    /**
     * @var \Magento\Catalog\Helper\Image
     */
    protected $imageHelper;

    /**
     * @var \Magento\Framework\UrlInterface
     */
    protected $urlBuilder;

    /**
     * @var FactoryInterface
     */
    protected $factoryInterface;
    /**
     * @var \Magento\Framework\View\Asset\Repository
     */
    protected $assetRepo;

    /**
     * @var \Magento\Store\Model\App\Emulation
     */
    protected $appEmulation;

    /**
     * @var storeManager
     */
    protected $storeManager;

    /**
     * @var \Magento\Customer\Model\Customer
     */
    protected $customer;

    /**
     * GiftCards constructor.
     * @param \Magento\Framework\Model\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param \MageWorx\GiftCards\Helper\Data $helper
     * @param TransportBuilder $transportBuilder
     * @param FactoryInterface $templateFactory
     * @param \Magento\Framework\View\Asset\Repository $assetRepo
     * @param \Magento\Framework\UrlInterface $urlBuilder
     * @param \Magento\Checkout\Helper\Data $checkoutHelper
     * @param \Magento\Catalog\Model\ProductFactory $productFactory
     * @param \Magento\Catalog\Helper\Image $imageHelper
     * @param \Magento\Store\Model\App\Emulation $appEmulation
     * @param StoreManager $storeManager
     * @param \Magento\Customer\Model\Customer $customer
     * @param \Magento\Framework\Model\ResourceModel\AbstractResource|null $resource
     * @param \Magento\Framework\Data\Collection\AbstractDb|null $resourceCollection
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\Model\Context $context,
        \Magento\Framework\Registry $registry,
        \MageWorx\GiftCards\Helper\Data $helper,
        TransportBuilder $transportBuilder,
        FactoryInterface $templateFactory,
        \Magento\Framework\View\Asset\Repository $assetRepo,
        \Magento\Framework\UrlInterface $urlBuilder,
        \Magento\Checkout\Helper\Data $checkoutHelper,
        \Magento\Catalog\Model\ProductFactory $productFactory,
        \Magento\Catalog\Helper\Image $imageHelper,
        \Magento\Store\Model\App\Emulation $appEmulation,
        StoreManager $storeManager,
        \Magento\Customer\Model\Customer $customer,
        \Magento\Framework\Model\ResourceModel\AbstractResource $resource = null,
        \Magento\Framework\Data\Collection\AbstractDb $resourceCollection = null,
        array $data = []
    ) {
        parent::__construct($context, $registry, $resource, $resourceCollection, $data);
        $this->transportBuilder = $transportBuilder;
        $this->factoryInterface = $templateFactory;
        $this->helper = $helper;
        $this->urlBuilder = $urlBuilder;
        $this->assetRepo = $assetRepo;
        $this->checkoutHelper = $checkoutHelper;
        $this->imageHelper = $imageHelper;
        $this->productFactory = $productFactory;
        $this->customer =$customer;
        $this->appEmulation = $appEmulation;
        $this->storeManager = $storeManager;
    }

    protected function _construct()
    {
        $this->_init('MageWorx\GiftCards\Model\ResourceModel\GiftCards');
    }

    /**
     * @return array
     */
    public function getAvailableStatuses()
    {
        return [
            self::STATUS_INACTIVE => __('Inactive'),
            self::STATUS_ACTIVE => __('Active'),
            self::STATUS_USED => __('Used')
        ];
    }

    /**
     * @return array
     */
    public function getCardTypes()
    {
        return [self::TYPE_EMAIL => __('Email'), self::TYPE_PRINT => __('Print'), self::TYPE_OFFLINE => __('Offline')];
    }

    /**
     * @return array
     */
    public function getIdentities()
    {
        return [self::CACHE_TAG . '_' . $this->getId()];
    }

    /**
     * @return string
     */
    public function getId()
    {
        return $this->getData(self::CARD_ID);
    }

    /**
     * @return string
     */
    public function getCardCode()
    {
        return $this->getData(self::CARD_CODE);
    }

    /**
     * @return string
     */
    public function getCustomerId()
    {
        return $this->getData(self::CUSTOMER_ID);
    }

    /**
     * @return string
     */
    public function getOrderId()
    {
        return $this->getData(self::ORDER_ID);
    }

    /**
     * @return string
     */
    public function getProductId()
    {
        return $this->getData(self::PRODUCT_ID);
    }

    /**
     * @return string
     */
    public function getCardCurrency()
    {
        return $this->getData(self::CARD_CURRENCY);
    }

    /**
     * @return string
     */
    public function getCardAmount()
    {
        return $this->getData(self::CARD_AMOUNT);
    }

    /**
     * @return string
     */
    public function getCardBalance()
    {
        return $this->getData(self::CARD_BALANCE);
    }

    /**
     * @return string
     */
    public function getCardStatus()
    {
        return $this->getData(self::CARD_STATUS);
    }

    /**
     * @return string
     */
    public function getCardStatusLabel()
    {
        $status = $this->getCardStatus();
        
        if ($status == self::STATUS_INACTIVE) {
            return self::STATUS_INACTIVE_LABEL;
        } elseif ($status == self::STATUS_ACTIVE) {
            return self::STATUS_ACTIVE_LABEL;
        } else {
            return self::STATUS_USED_LABEL;
        }
    }

    /**
     * @return string
     */
    public function getCardType()
    {
        return $this->getData(self::CARD_TYPE);
    }

    /**
     * @return string
     */
    public function getCardTypeLabel()
    {
        $type = $this->getCardType();
        
        if ($type == self::TYPE_EMAIL) {
            return self::TYPE_EMAIL_LABEL;
        } elseif ($type == self::TYPE_PRINT) {
            return self::TYPE_PRINT_LABEL;
        } else {
            return self::TYPE_OFFLINE_LABEL;
        }
    }

    /**
     * @return string
     */
    public function getMailFrom()
    {
        return $this->getData(self::MAIL_FROM);
    }

    /**
     * @return string
     */
    public function getMailTo()
    {
        return $this->getData(self::MAIL_TO);
    }

    /**
     * @return string
     */
    public function getMailToEmail()
    {
        return $this->getData(self::MAIL_TO_EMAIL);
    }

    /**
     * @return string
     */
    public function getMailMessage()
    {
        return $this->getData(self::MAIL_MESSAGE);
    }

    /**
     * @return string
     */
    public function getOfflineCountry()
    {
        return $this->getData(self::OFFLINE_COUNTRY);
    }

    /**
     * @return string
     */
    public function getOfflineState()
    {
        return $this->getData(self::OFFLINE_STATE);
    }

    /**
     * @return string
     */
    public function getOfflineCity()
    {
        return $this->getData(self::OFFLINE_CITY);
    }

    /**
     * @return string
     */
    public function getOfflineStreet()
    {
        return $this->getData(self::OFFLINE_STREET);
    }

    /**
     * @return string
     */
    public function getOfflineZip()
    {
        return $this->getData(self::OFFLINE_ZIP);
    }

    /**
     * @return string
     */
    public function getOfflinePhone()
    {
        return $this->getData(self::OFFLINE_PHONE);
    }

    /**
     * @return string
     */
    public function getMailDeliveryDate()
    {
        return $this->getData(self::MAIL_DELIVERY_DATE);
    }

    /**
     * @return string
     */
    public function getCreatedTime()
    {
        return $this->getData(self::CREATED_TIME);
    }

    /**
     * @return string
     */
    public function getUpdatedAt()
    {
        return $this->getData(self::UPDATED_AT);
    }

    /**
     * @return array
     */
    public function getStoreviewIds()
    {
        $result = $this->getData(self::STORE_ID);
        if (!is_array($result)) {
            if (empty($result)) {
                $result = [0];
            } else {
                $result = explode(',', $result);
            }
        }
        return $result;
    }

    /**
     * @return string
     */
    public function getExpiredDate()
    {
        return $this->getData(self::EXPIRE_DATE);
    }

    /**
     * @return boolean
     */
    public function getExpiredEmailSend()
    {
        return $this->getData(self::EXPIRED_EMAIL_SEND);
    }
    /**
     * @return boolean
     */
    public function getExpirationAlertEmailSend()
    {
        return $this->getData(self::EXPIRATION_ALERT_EMAIL_SEND);
    }

    /**
     * @param mixed $id
     * @return $this
     */
    public function setId($id)
    {
        return $this->setData(self::CARD_ID, $id);
    }

    /**
     * @param $cardCode
     * @return $this
     */
    public function setCardCode($cardCode)
    {
        return $this->setData(self::CARD_CODE, $cardCode);
    }

    /**
     * @param $customerId
     * @return $this
     */
    public function setCustomerId($customerId)
    {
        return $this->setData(self::CUSTOMER_ID, $customerId);
    }

    /**
     * @param $orderId
     * @return $this
     */
    public function setOrderId($orderId)
    {
        return $this->setData(self::ORDER_ID, $orderId);
    }

    /**
     * @param $productId
     * @return $this
     */
    public function setProductId($productId)
    {
        return $this->setData(self::PRODUCT_ID, $productId);
    }

    /**
     * @param $cardCurrency
     * @return $this
     */
    public function setCardCurrency($cardCurrency)
    {
        return $this->setData(self::CARD_CURRENCY, $cardCurrency);
    }

    /**
     * @param $cardAmount
     * @return $this
     */
    public function setCardAmount($cardAmount)
    {
        return $this->setData(self::CARD_AMOUNT, $cardAmount);
    }

    /**
     * @param $cardBalance
     * @return $this
     */
    public function setCardBalance($cardBalance)
    {
        return $this->setData(self::CARD_BALANCE, $cardBalance);
    }

    /**
     * @param $cardStatus
     * @return $this
     */
    public function setCardStatus($cardStatus)
    {
        return $this->setData(self::CARD_STATUS, $cardStatus);
    }

    /**
     * @param $cardType
     * @return $this
     */
    public function setCardType($cardType)
    {
        return $this->setData(self::CARD_TYPE, $cardType);
    }

    /**
     * @param $mailFrom
     * @return $this
     */
    public function setMailFrom($mailFrom)
    {
        return $this->setData(self::MAIL_FROM, $mailFrom);
    }

    /**
     * @param $mailTo
     * @return $this
     */
    public function setMailTo($mailTo)
    {
        return $this->setData(self::MAIL_TO, $mailTo);
    }

    /**
     * @param $mailToEmail
     * @return $this
     */
    public function setMailToEmail($mailToEmail)
    {
        return $this->setData(self::MAIL_TO_EMAIL, $mailToEmail);
    }

    /**
     * @param $mailMessage
     * @return $this
     */
    public function setMailMessage($mailMessage)
    {
        return $this->setData(self::MAIL_MESSAGE, $mailMessage);
    }

    /**
     * @param $offlineCountry
     * @return $this
     */
    public function setOfflineCountry($offlineCountry)
    {
        return $this->setData(self::OFFLINE_COUNTRY, $offlineCountry);
    }

    /**
     * @param $offlineState
     * @return $this
     */
    public function setOfflineState($offlineState)
    {
        return $this->setData(self::OFFLINE_STATE, $offlineState);
    }

    /**
     * @param $offlineCity
     * @return $this
     */
    public function setOfflineCity($offlineCity)
    {
        return $this->setData(self::OFFLINE_CITY, $offlineCity);
    }

    /**
     * @param $offlineStreet
     * @return $this
     */
    public function setOfflineStreet($offlineStreet)
    {
        return $this->setData(self::OFFLINE_STREET, $offlineStreet);
    }

    /**
     * @param $offlineZip
     * @return $this
     */
    public function setOfflineZip($offlineZip)
    {
        return $this->setData(self::OFFLINE_ZIP, $offlineZip);
    }

    /**
     * @param $offlinePhone
     * @return $this
     */
    public function setOfflinePhone($offlinePhone)
    {
        return $this->setData(self::OFFLINE_PHONE, $offlinePhone);
    }

    /**
     * @param $mailDeliveryDate
     * @return $this
     */
    public function setMailDeliveryDate($mailDeliveryDate)
    {
        return $this->setData(self::MAIL_DELIVERY_DATE, $mailDeliveryDate);
    }

    /**
     * @param $createdTime
     * @return $this
     */
    public function setCreatedTime($createdTime)
    {
        return $this->setData(self::CREATED_TIME, $createdTime);
    }

    /**
     * @param $updatedAt
     * @return $this
     */
    public function setUpdatedAt($updatedAt)
    {
        return $this->setData(self::UPDATED_AT, $updatedAt);
    }

    /**
     * @param string|array $storeviewIds
     * @return $this
     */
    public function setStoreviewIds($storeviewIds = '')
    {
        if (!is_array($storeviewIds)) {
            $storeviewIds = explode(',', $storeviewIds);
        }
        return $this->setData(self::STORE_ID, $storeviewIds);
    }

    /**
     * @param $expireDate
     * @return $this
     */
    public function setExpiredDate($expireDate)
    {
        return $this->setData(self::EXPIRE_DATE, $expireDate);
    }

    /**
     * @param $expireEmailSend
     * @return $this
     */
    public function setExpiredEmailSend($expireEmailSend)
    {
        return $this->setData(self::EXPIRED_EMAIL_SEND, $expireEmailSend);
    }

    /**
     * @param $expirationAlertEmailSend
     * @return $this
     */
    public function setExpirationAlertEmailSend($expirationAlertEmailSend)
    {
        return $this->setData(self::EXPIRATION_ALERT_EMAIL_SEND, $expirationAlertEmailSend);
    }

    public function activateCard()
    {
        $this->setCardStatus(self::STATUS_ACTIVE);
        return true;
    }

    public function sendNoOrder()
    {
        $templateVars = [
             'amount'        => $this->checkoutHelper->formatPrice($this->getCardAmount()),
             'code'          => $this->getCardCode(),
             'email-to'      => $this->getMailTo(),
             'email-from'    => $this->getMailFrom(),
             'recipient'     => $this->getMailToEmail(),
             'email-message' => nl2br($this->getMailMessage()),
             'store-phone'   => $this->helper->getStorePhone(),
             'picture'       => $this->getGiftCardPicture(),
            'alert-days'     => $this->helper->getAlertDays()
        ];

        $this->transportBuilder->setTemplateIdentifier('mageworx_giftcards_email_email_template');
        $this->transportBuilder->setTemplateVars($templateVars);
        $this->transportBuilder->setFrom('general');
        $this->transportBuilder->addTo($this->getMailToEmail(), $this->getMailTo());
        $this->transportBuilder->setTemplateOptions([
            'area' => \Magento\Framework\App\Area::AREA_FRONTEND,
            'store' => $this->storeManager->getDefaultStoreView()->getId(),
        ]);
    }

    /**
     * @param \Magento\Sales\Model\Order $order
     * @return void
     */
    public function send(\Magento\Sales\Model\Order $order = null)
    {
        $type = $this->getCardType();
        switch ($type) {
            case self::TYPE_EMAIL:
                $this->prepareEmailCard($order);
                break;
            case self::TYPE_PRINT:
                $this->preparePrintCard($order);
                break;
            case self::TYPE_OFFLINE:
                $this->prepareOfflineCard($order);
                break;
            default:
                $this->prepareEmailCard($order);
                break;
        }
        $transport = $this->transportBuilder->getTransport();
        $transport->sendMessage();
    }

    /**
     * @param \Magento\Sales\Model\Order $order
     * @return $this
     */
    protected function prepareEmailCard($order)
    {
        $templateVars = [
             'amount'        => $this->checkoutHelper->formatPrice($this->getCardAmount()),
             'code'          => $this->getCardCode(),
             'email-to'      => $this->getMailTo(),
             'email-from'    => $this->getMailFrom(),
             'recipient'     => $this->getMailToEmail(),
             'email-message' => nl2br($this->getMailMessage()),
             'store-phone'   => $this->helper->getStorePhone(),
             'picture'       => $this->getGiftCardPicture($order->getStoreId()),
        ];

        $this->transportBuilder->setTemplateIdentifier('mageworx_giftcards_email_email_template');
        $this->transportBuilder->setTemplateVars($templateVars);
        $this->transportBuilder->setFrom('general');
        $this->transportBuilder->addTo($this->getMailToEmail(), $this->getMailTo());
        $this->transportBuilder->setTemplateOptions([
            'area' => \Magento\Framework\App\Area::AREA_FRONTEND,
            'store' => $order->getStoreId()
        ]);

        return $this;
    }

    /**
     * @param \Magento\Sales\Model\Order $order
     * @return $this
     */
    protected function preparePrintCard($order)
    {
        $templateVars = [
             'amount'        => $this->checkoutHelper->formatPrice($this->getCardAmount()),
             'code'          => $this->getCardCode(),
             'email-to'      => $this->getMailTo(),
             'email-from'    => $this->getMailFrom(),
             'recipient'     => $order->getCustomerEmail(),
             'email-message' => nl2br($this->getMailMessage()),
             'store-phone'   => $this->helper->getStorePhone(),
             'picture'       => $this->getGiftCardPicture($order->getStoreId()),
             'link'          => $this->urlBuilder->getBaseUrl() . 'mageworx_giftcards/giftcards/printCard/code/'. $this->getCardCode(),
             'customer-name' => $order->getCustomerName()
        ];

        $this->transportBuilder->setTemplateIdentifier('mageworx_giftcards_email_print_template');
        $this->transportBuilder->setTemplateVars($templateVars);
        $this->transportBuilder->setFrom('general');
        $this->transportBuilder->addTo($order->getCustomerEmail(), $this->getMailTo());
        $this->transportBuilder->setTemplateOptions([
            'area' => \Magento\Framework\App\Area::AREA_FRONTEND,
            'store' => $order->getStoreId()
        ]);

        return $this;
    }

    /**
     * @param \Magento\Sales\Model\Order $order
     * @return $this
     */
    protected function prepareOfflineCard($order)
    {
        $templateVars = [
             'amount'        => $this->checkoutHelper->formatPrice($this->getCardAmount()),
             'code'          => $this->getCardCode(),
             'email-to'      => $this->getMailTo(),
             'email-from'    => $this->getMailFrom(),
             'recipient'     => $order->getCustomerEmail(),
             'email-message' => nl2br($this->getMailMessage()),
             'store-phone'   => $this->helper->getStorePhone(),
             'picture'       => $this->getGiftCardPicture($order->getStoreId()),
        ];

        $this->transportBuilder->setTemplateIdentifier('mageworx_giftcards_email_offline_template');
        $this->transportBuilder->setTemplateVars($templateVars);
        $this->transportBuilder->setFrom('general');
        $this->transportBuilder->addTo($order->getCustomerEmail(), $this->getMailTo());
        $this->transportBuilder->setTemplateOptions([
            'area' => \Magento\Framework\App\Area::AREA_FRONTEND,
            'store' => $order->getStoreId()
        ]);

        return $this;
    }

    /**
     * @param \Magento\Sales\Model\Order $order
     * @return $this
     */
    public function sendExpiredCard()
    {
        if ($this->getMailToEmail()) {
            $templateVars = [
                'amount'        => $this->checkoutHelper->formatPrice($this->getCardAmount()),
                'code'          => $this->getCardCode(),
                'email-to'      => $this->getMailTo(),
                'email-from'    => $this->getMailFrom(),
                'email-message' => nl2br($this->getMailMessage()),
                'store-phone'   => $this->helper->getStorePhone()
            ];

            $this->transportBuilder->setTemplateIdentifier('mageworx_giftcards_email_expired_template');
            $this->transportBuilder->setTemplateVars($templateVars);
            $this->transportBuilder->setFrom('general');
            $this->transportBuilder->addTo($this->getMailToEmail(), $this->getMailTo());
            $this->transportBuilder->setTemplateOptions([
                'area' => \Magento\Framework\App\Area::AREA_FRONTEND,
                'store' => $this->storeManager->getDefaultStoreView()->getId(),
            ]);

            $transport = $this->transportBuilder->getTransport();
            $transport->sendMessage();
        }
        return $this;
    }

    /**
     * @param \Magento\Sales\Model\Order $order
     * @return $this
     */
    public function sendCardExpirationAlert()
    {
        if ($this->getMailToEmail()) {
            $templateVars = [
                'amount' => $this->checkoutHelper->formatPrice($this->getCardAmount()),
                'code' => $this->getCardCode(),
                'email-to' => $this->getMailTo(),
                'email-from' => $this->getMailFrom(),
                'email-message' => nl2br($this->getMailMessage()),
                'store-phone' => $this->helper->getStorePhone(),
                'alert-days' => $this->helper->getAlertDays(),
                'expire-date' => $this->getExpiredDate()
            ];

            $this->transportBuilder->setTemplateIdentifier('mageworx_giftcards_email_expiration_alert_template');
            $this->transportBuilder->setTemplateVars($templateVars);
            $this->transportBuilder->setFrom('general');
            $this->transportBuilder->addTo($this->getMailToEmail(), $this->getMailTo());
            $this->transportBuilder->setTemplateOptions([
                'area' => \Magento\Framework\App\Area::AREA_FRONTEND,
                'store' => $this->storeManager->getDefaultStoreView()->getId(),
            ]);

            $transport = $this->transportBuilder->getTransport();
            $transport->sendMessage();
        }
        return $this;
    }

    /**
     * @param int|null $storeId
     * @return string
     */
    protected function getGiftCardPicture($storeId = null)
    {
        if ($storeId !== null) {
            $this->appEmulation->startEnvironmentEmulation($storeId, \Magento\Framework\App\Area::AREA_FRONTEND, true);
        }

        if ($productId = $this->getProductId()) {
            $product = $this->productFactory->create()->load($productId);
            $picture = $this->imageHelper->init($product, 'mageworx_giftcards_main_image')->getUrl();
        } else {
            $picture = $this->assetRepo->getUrl('MageWorx_GiftCards::images/giftcard.png');
        }
        
        if (strpos($picture, 'placeholder') !== false) {
            $picture = $this->assetRepo->getUrl('MageWorx_GiftCards::images/giftcard.png');
        }

        if ($storeId !== null) {
            $this->appEmulation->stopEnvironmentEmulation();
        }

        return $picture;
    }
    
    public function preview($data)
    {
        if ($productId = $data['product']) {
            $product = $this->productFactory->create()->load($productId);
            $picture = $this->imageHelper->init($product, 'mageworx_giftcards_main_image')->getUrl();
        } else {
            $picture = $this->assetRepo->getUrl('MageWorx_GiftCards::images/giftcard.png');
        }

        if (strpos($picture, 'placeholder') === true) {
            $picture = $this->assetRepo->getUrl('MageWorx_GiftCards::images/giftcard.png');
        }
        
        $templateVars = [
             'amount'        => $this->checkoutHelper->formatPrice($data['price']),
             'code'          => 'XXXX-XXXX-XXXX',
             'email-to'      => $data['mailTo'],
             'email-from'    => $data['mailFrom'],
             'recipient'     => $data['mailToEmail'],
             'email-message' => nl2br($data['mailMessage']),
             'picture'       => $picture,
        ];

        $template = $this->factoryInterface->get('mageworx_giftcards_email_email_template', '')
            ->setVars($templateVars)
            ->setOptions([
            'area' => \Magento\Framework\App\Area::AREA_FRONTEND,
            'store' => 1
            ]);
            
        $body = $template->processTemplate();
        return $body;
    }
    
    public function getPictureUrl()
    {
        return $this->getGiftCardPicture();
    }
}
