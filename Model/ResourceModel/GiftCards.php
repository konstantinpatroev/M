<?php
/**
 * Copyright © 2016 MageWorx. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace MageWorx\GiftCards\Model\ResourceModel;

class GiftCards extends \Magento\Framework\Model\ResourceModel\Db\AbstractDb
{

    /**
     * Store model
     *
     * @var null|\Magento\Store\Model\Store
     */
    protected $store = null;

    /**
     * Store manager
     *
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $storeManager;

    /**
     * @var \Magento\Framework\Stdlib\DateTime\DateTime
     */
    protected $date;

    protected $tables;

    public function __counstruct(
        \Magento\Framework\Model\Resource\Db\Context $context,
        \Magento\Framework\Stdlib\DateTime\DateTime $date,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        $resourcePrefix = null
    ) {
        parent::__construct($context, $resourcePrefix);
        $this->date = $date;
        $this->storeManager = $storeManager;
    }


    protected function _construct()
    {
        $this->_init('mageworx_giftcards_card', 'card_id');
        $this->date = date('Y-m-d H:i:s', time());
        $this->tables = [
            'store_id'          => 'mageworx_giftcards_store',
            'customer_group_id' => 'mageworx_giftcards_customer_group'
        ];
    }

    /**
     * Process page data before deleting
     *
     * @param \Magento\Framework\Model\AbstractModel $object
     * @return $this
     */
    protected function _beforeDelete(\Magento\Framework\Model\AbstractModel $object)
    {
        $condition = ['card_id = ?' => (int)$object->getId()];

        foreach ($this->tables as $id => $table) {
            $this->getConnection()->delete($this->getTable($table), $condition);
        }

        return parent::_beforeDelete($object);
    }

    /**
     * Assign page to store views
     *
     * @param \Magento\Framework\Model\AbstractModel $object
     * @return $this
     */
    protected function _afterSave(\Magento\Framework\Model\AbstractModel $object)
    {
        foreach ($this->tables as $id => $table) {
            $old = $this->lookupIds($object->getId(), $id, $table);
            $new = $object->getData($id);

            $table = $this->getTable($table);
            if ($new && is_array($old) && is_array($new)) {
                $insert = array_diff($new, $old);
                $delete = array_diff($old, $new);

                if ($delete) {
                    $where = ['card_id = ?' => (int)$object->getId(), $id . ' IN (?)' => $delete];

                    $this->getConnection()->delete($table, $where);
                }

                if ($insert) {
                    $data = [];

                    foreach ($insert as $dataId) {
                        $data[] = ['card_id' => (int)$object->getId(), $id  => (int)$dataId];
                    }

                    $this->getConnection()->insertMultiple($table, $data);
                }
            }
        }
        return parent::_afterSave($object);
    }

    /**
     * Perform operations after object load
     *
     * @param \Magento\Framework\Model\AbstractModel $object
     * @return $this
     */
    protected function _afterLoad(\Magento\Framework\Model\AbstractModel $object)
    {
        if ($object->getId()) {
            foreach ($this->tables as $id => $table) {
                $items = $this->lookupIds($object->getId(), $id, $table);
                $object->setData($id, $items);
            }
        }

        return parent::_afterLoad($object);
    }

    /**
     * @param \Magento\Framework\Model\AbstractModel $object
     * @return $this
     */
    protected function _beforeSave(\Magento\Framework\Model\AbstractModel $object)
    {
        if ($object->isObjectNew() && !$object->hasCardCode()) {
            if (!$object->getCardBalance()) {
                $object->setCardBalance($object->getCardAmount());
            }
            $object->setCardCode($this->_getUniqueCardCode());
            $object->setCreatedTime($this->date);
        }

        if (strlen($object->getCardCurrency()) != 3) {
            $object->setCardCurrency('');
        }

        $object->setCardCurrency(strtoupper($object->getCardCurrency()));

        $object->setUpdatedAt($this->date);

        return parent::_beforeSave($object);
    }

    public function load(\Magento\Framework\Model\AbstractModel $object, $value, $field = null)
    {
        if (!is_numeric($value) && $field === null) {
            $field = 'card_code';
        }

        return parent::load($object, $value, $field);
    }

    /**
     * Get Gift Card identifier by Gift Card Code
     *
     * @param string $giftCardCode
     * @return int|false
     */
    public function getIdByCardCode($giftCardCode)
    {
        $connection = $this->getConnection();

        $select = $connection->select()->from($this->getMainTable(), 'card_id')->where('card_code = :giftCardCode');

        $bind = [':giftCardCode' => (string)$giftCardCode];

        return $connection->fetchOne($select, $bind);
    }

    /**
     * Retrieve select object for load object data
     *
     * @param string $field
     * @param mixed $value
     * @param \Magento\Cms\Model\Page $object
     * @return \Magento\Framework\DB\Select
     */
   /* protected function _getLoadSelect($field, $value, $object)
    {
        $select = parent::_getLoadSelect($field, $value, $object);

        foreach ($this->tables as $id => $table) {
                $select->join(
                [$table . '_table' => $this->getTable($table)],
                    $this->getMainTable() . '.card_id = ' . $table . '_table.card_id',
                    []
                );
        }

        return $select;
    }*/

    /**
     * Get store ids to which specified item is assigned
     * @param $cardId
     * @param $id
     * @param $table
     * @return array
     */
    public function lookupIds($cardId, $id, $table)
    {
        $connection = $this->getConnection();

        $select = $connection->select()->from(
            $this->getTable($table),
            $id
        )->where(
            'card_id = ?',
            (int)$cardId
        );

        return $connection->fetchCol($select);
    }

    /**
     * Set store model
     *
     * @param \Magento\Store\Model\Store $store
     * @return $this
     */
    public function setStore($store)
    {
        $this->store = $store;
        return $this;
    }

    /**
     * Retrieve store model
     *
     * @return \Magento\Store\Model\Store
     */
    public function getStore()
    {
        return $this->storeManager->getStore($this->store);
    }

    /**
     * Retrive load select with filter bu card_code and card_state
     *
     * @param $cardCode
     * @param null $state
     * @return \Magento\Framework\DB\Select
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    protected function _getLoadByCardCodeSelect($cardCode, $state = null)
    {
        $select = $this->getConnection()->select()
            ->from(['giftcards' => $this->getMainTable()])
            ->where('giftcards.card_code = ?', $cardCode);
        if ($state !== null) {
            $select->where('giftcards.card_state = ?', $state);
        }

        return $select;
    }

    public function loadCustomerCardsWithOrders($email)
    {
        $connection = $this->getConnection();
        $select = $connection->select()
            ->from(['giftcards' => $this->getMainTable()])
            ->where('giftcards.mail_to_email = ? and orders.discounted is not null', $email)
            ->joinLeft(
                ['orders' => $this->getTable('mageworx_giftcard_order')],
                'orders.giftcard_id = giftcards.card_id',
                [
                    'apply_order_id' => 'orders.order_id',
                    'discounted'     => 'orders.discounted',
                    'apply_created_time' => 'orders.created_time'
                ]
            );

        $data = $connection->fetchAll($select);
        return $data;
    }

    protected function _getUniqueCardCode()
    {
        $characters = '0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $mask = '#####-#####-#####';

        $cardCode = $mask;
        while (strpos($cardCode, '#') !== false) {
            $cardCode = substr_replace($cardCode, $characters[mt_rand(0, strlen($characters)-1)], strpos($cardCode, '#'), 1);
        }

        return $cardCode;
    }
}
