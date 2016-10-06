<?php
namespace Straker\EasyTranslationPlatform\Model\ResourceModel\AttributeTranslation;

use Magento\Catalog\Api\Data\CategoryAttributeInterface;
use Magento\Eav\Api\AttributeRepositoryInterface;
use Magento\Framework\Data\Collection\Db\FetchStrategyInterface;
use Magento\Framework\Data\Collection\EntityFactoryInterface;
use Magento\Framework\Event\ManagerInterface;
use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;
use Psr\Log\LoggerInterface;
use Straker\EasyTranslationPlatform\Model;
use Zend_Db_Select;

class Collection extends AbstractCollection
{
    protected $_attributeRepository;

    function __construct(
        EntityFactoryInterface $entityFactory,
        LoggerInterface $logger,
        FetchStrategyInterface $fetchStrategy,
        ManagerInterface $eventManager,
        AttributeRepositoryInterface $attributeRepository
    ) {
        parent::__construct($entityFactory, $logger, $fetchStrategy, $eventManager);
        $this->_attributeRepository = $attributeRepository;
    }

    protected function _construct()
    {
        $this->_init('Straker\EasyTranslationPlatform\Model\AttributeTranslation',
            'Straker\EasyTranslationPlatform\Model\ResourceModel\AttributeTranslation');
    }

    public function massUpdate(array $data)
    {
        $this->getConnection()->update($this->getResource()->getMainTable(), $data, $this->getResource()->getIdFieldName() . ' IN(' . implode(',', $this->getAllIds()) . ')');

        return $this;
    }

    /**
     * @param int $sourceStoreId
     * @return $this
     * @internal param int $attrId
     */
    function addCategoryName( $sourceStoreId = 0 ){
        $categoryTable = $this->getTable('catalog_category_entity_varchar');
        $nameAttribute = $this->_attributeRepository->get(CategoryAttributeInterface::ENTITY_TYPE_CODE, 'name');
        $attrId = $nameAttribute->getAttributeId();
        if( $sourceStoreId == 0 ){
            $this->getSelect()
                ->joinLeft(
                    ['cn'=> $categoryTable],
                    'main_table.entity_id = cn.entity_id AND cn.store_id = 0 AND cn.attribute_id = ' . $attrId,
                    ['name' => 'value']
                );
        }else{
            $this->getSelect()
                ->columns(
                    'if(cn_store.value IS NOT NULL, cn_store.value, cn_default.value) AS name'
                )->joinLeft(
                    ['cn_store'=> $categoryTable],
                    'main_table.entity_id = cn_store.entity_id AND cn_store.store_id = ' .$sourceStoreId . ' AND cn_store.attribute_id = ' . $attrId,
                    []
                )->joinLeft(
                    ['cn_default'=> $categoryTable],
                    'main_table.entity_id = cn_default.entity_id AND cn_default.store_id = 0 AND cn_default.attribute_id = ' . $attrId,
                    []
                );
        }

        return $this;
    }


    function getSelectCountSql()
    {
        $this->_renderFilters();
        $countSelect = clone $this->getSelect();
        $countSelect->reset(\Magento\Framework\DB\Select::ORDER);
        $countSelect->reset(\Magento\Framework\DB\Select::LIMIT_COUNT);
        $countSelect->reset(\Magento\Framework\DB\Select::LIMIT_OFFSET);
        $countSelect->reset(\Magento\Framework\DB\Select::COLUMNS);
        $countSelect->reset(\Magento\Framework\DB\Select::FROM);
        $countSelect->reset(\Magento\Framework\DB\Select::WHERE);

        $select = clone $this->getSelect();
        $select->reset(\Magento\Framework\DB\Select::ORDER);
        $select->reset(\Magento\Framework\DB\Select::LIMIT_COUNT);
        $select->reset(\Magento\Framework\DB\Select::LIMIT_OFFSET);

        $countSelect->from(
            ['s' => $select ]
        );
        $countSelect->reset(\Magento\Framework\DB\Select::COLUMNS);
        $countSelect->reset(\Magento\Framework\DB\Select::HAVING);
        $countSelect->columns(new \Zend_Db_Expr('COUNT(*)'));
        return $countSelect;
    }
}
