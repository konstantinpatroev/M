<?php
/**
 * Copyright © 2016 MageWorx. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace MageWorx\GiftCards\Model\ResourceModel\GiftCards;

use Magento\Store\Model\Store;

class Collection extends \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection
{
    protected $helper;

    /**
     * Store manager
     *
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $storeManager;

    /**
     * @var string
     */
    protected $_idFieldName = 'card_id';

    /**
     * Load data for preview flag
     *
     * @var bool
     */
    protected $_previewFlag;

    /**
     * Collection constructor.
     * @param \MageWorx\GiftCards\Helper\Data $helper
     * @param \Magento\Framework\Data\Collection\EntityFactoryInterface $entityFactory
     * @param \Psr\Log\LoggerInterface $logger
     * @param \Magento\Framework\Data\Collection\Db\FetchStrategyInterface $fetchStrategy
     * @param \Magento\Framework\Event\ManagerInterface $eventManager
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param \Magento\Framework\DB\Adapter\AdapterInterface|null $connection
     * @param \Magento\Framework\Model\ResourceModel\Db\AbstractDb|null $resource
     */
    public function __construct(
        \MageWorx\GiftCards\Helper\Data $helper,
        \Magento\Framework\Data\Collection\EntityFactoryInterface $entityFactory,
        \Psr\Log\LoggerInterface $logger,
        \Magento\Framework\Data\Collection\Db\FetchStrategyInterface $fetchStrategy,
        \Magento\Framework\Event\ManagerInterface $eventManager,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Framework\DB\Adapter\AdapterInterface $connection = null,
        \Magento\Framework\Model\ResourceModel\Db\AbstractDb $resource = null
    ) {
    
        $this->helper = $helper;
        $this->storeManager = $storeManager;
        parent::__construct($entityFactory, $logger, $fetchStrategy, $eventManager, $connection, $resource);
    }

    /**
     * Define resource model
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('MageWorx\GiftCards\Model\GiftCards', 'MageWorx\GiftCards\Model\ResourceModel\GiftCards');
        $this->_map['fields']['card_id'] = 'main_table.card_id';
        $this->_map['fields']['store'] = 'store_table.store_id';
        $this->_map['fields']['customer_group'] = 'customer_group_table.customer_group_id';
    }


    /**
     * Perform operations after collection load
     *
     * @param string $tableName
     * @param string $columnName
     * @return void
     */
    protected function performAfterLoad($tableName, $columnName)
    {
        $items = $this->getColumnValues('card_id');

        if (count($items)) {
            $connection = $this->getConnection();
            $select = $connection->select()->from(['card_table' => $this->getTable($tableName)])
                ->where('card_table.card_id IN (?)', $items);
            $result = $connection->fetchAll($select);

            if ($result) {
                foreach ($this as $item) {
                    $entityId = $item->getData('card_id');
                    $entityIds = [];
                    foreach ($result as $value) {
                        if ($value['card_id'] != $entityId) {
                            continue;
                        }
                        $entityIds[] = $value[$columnName];
                    }
                    $item->setData($columnName, $entityIds);
                }
            }
        }
    }

    /**
     * Add field filter to collection
     *
     * @param array|string $field
     * @param string|int|array|null $condition
     * @return $this
     */
    public function addFieldToFilter($field, $condition = null)
    {
        if ($field === 'store_id') {
            return $this->addStoreFilter($condition, false);
        }

        if ($field === 'customer_group_id') {
            return $this->addCustomerGroupFilter($condition, false);
        }

        return parent::addFieldToFilter($field, $condition);
    }


    /**
     * Set first store flag
     *
     * @param bool $flag
     * @return $this
     */
    public function setFirstStoreFlag($flag = false)
    {
        $this->_previewFlag = $flag;
        return $this;
    }

    /**
     * Add filter by store
     *
     * @param int|array|\Magento\Store\Model\Store $store
     * @param bool $withAdmin
     * @return $this
     */
    public function addStoreFilter($store, $withAdmin = true)
    {
        if (!$this->getFlag('store_filter_added')) {
            $this->performAddStoreFilter($store, $withAdmin);
        }
        return $this;
    }

    /**
     * Add filter by customer group
     *
     * @param int|array|\Magento\Store\Model\Store $store
     * @param bool $withAdmin
     * @return $this
     */
    public function addCustomerGroupFilter($customerGroup, $withAdmin = true)
    {
        if (!$this->getFlag('customer_group_added')) {
            $this->performAddCustomerGroupFilter($customerGroup, $withAdmin);
        }
        return $this;
    }

    /**
     * Perform operations after collection load
     *
     * @return $this
     */
    protected function _afterLoad()
    {
        $this->performAfterLoad('mageworx_giftcards_store', 'store_id');
        $this->performAfterLoad('mageworx_giftcards_customer_group', 'customer_group_id');
        $this->_previewFlag = false;

        return parent::_afterLoad();
    }

    /**
     * Perform operations before rendering filters
     *
     * @return void
     */
    protected function _renderFiltersBefore()
    {
        $this->joinRelationTables('mageworx_giftcards_store', 'card_id', 'store');
        $this->joinRelationTables('mageworx_giftcards_customer_group', 'customer_group_id', 'customer_group');
    }

    /**
     * Perform adding filter by store
     *
     * @param int|array|\Magento\Store\Model\Store $store
     * @param bool $withAdmin
     * @return void
     */
    protected function performAddStoreFilter($store, $withAdmin = true)
    {
        if ($store instanceof \Magento\Store\Model\Store) {
            $store = [$store->getId()];
        }

        if (!is_array($store)) {
            $store = [$store];
        }

        if ($withAdmin) {
            $store[] = \Magento\Store\Model\Store::DEFAULT_STORE_ID;
        }

        $this->addFilter('store', ['in' => $store], 'public');
    }

    /**
     * Perform adding filter by store
     *
     * @param int|array|\Magento\Store\Model\Store $store
     * @param bool $withAdmin
     * @return void
     */
    protected function performAddCustomerGroupFilter($customerGroup)
    {
        if (!is_array($customerGroup)) {
            $customerGroup = [$customerGroup];
        }

        $this->addFilter('customer_group', ['in' => $customerGroup], 'public');
    }

    /**
     * Join store relation table if there is store filter
     *
     * @param string $tableName
     * @param string $columnName
     * @return void
     */
    protected function joinRelationTables($tableName, $columnName, $filter)
    {
        if ($this->getFilter($filter)) {
            $this->getSelect()->join(
                ['table' => $this->getTable($tableName)],
                'main_table.' . $columnName . ' = table.' . $columnName,
                []
            )->group(
                'main_table.' . $columnName
            );
        }
        parent::_renderFiltersBefore();
    }

    /**
     * Add type filter
     *
     * @return this
     */
    public function addEnabledFilter()
    {
        return $this->getSelect()->where('main_table.card_status = 1');
    }
}
