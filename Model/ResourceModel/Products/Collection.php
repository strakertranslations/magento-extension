<?php
namespace Straker\EasyTranslationPlatform\Model\ResourceModel\Products;

use Magento\Framework\DB\Select;

/**
 * Factory class for @see \Magento\Catalog\Model\ResourceModel\Product\Collection
 */
class Collection extends \Magento\Catalog\Model\ResourceModel\Product\Collection
{

    public function is_translated($target_store_id=1)
    {

        $strakerJobs = $this->_resource->getTableName('straker_job');

        $strakerTrans = $this->_resource->getTableName('straker_attribute_translation');

        $this->getSelect()->columns(
            'if(stTrans.is_imported IS NULL,0,1) as is_translated'
        )->joinLeft(
            ['stTrans' => $strakerTrans],
            'e.entity_id=stTrans.entity_id',
            []
        );

        $this->getSelect()->columns(
            'stJob.job_id'
        )->joinLeft(
            ['stJob' => $strakerJobs],
            'stTrans.job_id=stJob.job_id and stJob.target_store_id='.$target_store_id.' and stJob.job_type_id=1',
            []
        )->group('entity_id');

        return $this;
    }

    public function getSelectCountSql()
    {
//         parent::getSelectCountSql();
        $this->_renderFilters();
        $countSelect = clone $this->getSelect();
        $countSelect->reset(Select::ORDER);
        $countSelect->reset(Select::LIMIT_COUNT);
        $countSelect->reset(Select::LIMIT_OFFSET);
        $countSelect->reset(Select::COLUMNS);
        $countSelect->reset(Select::FROM);
        $countSelect->reset(Select::WHERE);
        $select = clone $this->getSelect();
        $select->reset(Select::ORDER);
        $select->reset(Select::LIMIT_COUNT);
        $select->reset(Select::LIMIT_OFFSET);
        $countSelect->from(
            ['s' => $select]
        );
        $countSelect->reset(Select::COLUMNS);
        $countSelect->reset(Select::HAVING);
        $countSelect->reset(Select::GROUP);
        $group = $this->getSelect()->getPart(Select::GROUP);
        $countSelect->columns(new \Zend_Db_Expr(("COUNT(DISTINCT " . implode(", ", $group) . ")")));
        return $countSelect;
    }
}
