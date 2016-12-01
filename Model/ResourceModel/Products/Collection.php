<?php
namespace Straker\EasyTranslationPlatform\Model\ResourceModel\Products;

use Magento\Framework\DB\Select;
use Magento\Framework\DB\SelectFactory;
use Straker\EasyTranslationPlatform\Model\JobType;

/**
 * Factory class for @see \Magento\Catalog\Model\ResourceModel\Product\Collection
 */
class Collection extends \Magento\Catalog\Model\ResourceModel\Product\Collection
{

    public function is_translated($target_store_id)
    {
        $strakerJobs = $this->_resource->getTableName('straker_job');
        $strakerTrans = $this->_resource->getTableName('straker_attribute_translation');

        $this->getSelect()
            ->reset(Select::COLUMNS)
            ->columns(
                ['e.entity_id', 'e.sku', 'MAX(IF((stTrans.is_published AND stJob.job_id) IS NULL, 0, 1)) as is_translated']
            )->joinLeft(
                ['stTrans' => $strakerTrans],
                'e.entity_id=stTrans.entity_id',
                []
            )->joinLeft(
                ['stJob' => $strakerJobs],
                'stTrans.job_id=stJob.job_id and stJob.target_store_id=' . $target_store_id . ' and stJob.job_type_id='. JobType::JOB_TYPE_PRODUCT,
                []
            )->group('entity_id');

//        var_dump($this->getSelect()->__toString());exit;
        return $this;
    }

    public function getSelectCountSql()
    {
//         parent::getSelectCountSql();
        $this->_renderFilters();
        $countSelect = clone $this->getSelect();

        $select = clone $this->getSelect();
        $select
            ->reset(Select::ORDER)
            ->reset(Select::LIMIT_COUNT)
            ->reset(Select::LIMIT_OFFSET);

        $countSelect
            ->reset()
            ->from(['s' => $select])
            ->reset(Select::COLUMNS)
            ->columns('COUNT(DISTINCT entity_id)');
//        var_dump($countSelect->__toString());
//        $select->reset(Select::COLUMNS)->columns('e.entity_id');
//        $select->reset(Select::HAVING);
//        $select->reset(Select::GROUP);
//        $group = $this->getSelect()->getPart(Select::GROUP);
//        $countSelect->columns(new \Zend_Db_Expr(("COUNT(DISTINCT " . implode(", ", $group) . ")")));
//        $countSelect->columns(new \Zend_Db_Expr(("COUNT(DISTINCT s.entity_id)")));

//        var_dump($countSelect->__toString());

        return $countSelect;
    }

    function _buildClearSelect($select = null)
    {
        if (null === $select) {
            $select = clone $this->getSelect();
        }
        $select->reset(\Magento\Framework\DB\Select::ORDER);
        return $select;
    }

}
