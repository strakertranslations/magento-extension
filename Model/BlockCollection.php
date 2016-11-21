<?php
/**
 * Created by PhpStorm.
 * User: rakeshmistry
 * Date: 17/11/16
 * Time: 11:56 AM
 */

namespace Straker\EasyTranslationPlatform\Model;

use Magento\Framework\DB\Select;

class BlockCollection extends \Magento\Cms\Model\ResourceModel\Block\Collection
{

    public function is_translated($target_store_id,$source_store_id)
    {

        $strakerJobs = $this->_resource->getTable('straker_job');

        $strakerTrans = $this->_resource->getTable('straker_attribute_translation');

        $cmsBlockStore = $this->_resource->getTable('cms_block_store');

        $this->getSelect()->reset(Select::COLUMNS);

        $this->getSelect()->columns(new \Zend_Db_Expr('block_id,title,content, b.*, IF(max(b.is_imported) IS NULL, 0, 1) as is_translated'));

        $subquery = clone $this->getSelect();

        $subquery->reset();

        $subquery->from(['stJob'=>$this->getTable('straker_job')]);

        $subquery->joinLeft(
            ['stTrans'=>$this->getTable('straker_attribute_translation')],
            'stTrans.job_id=stJob.job_id',
            ['stTrans.entity_id','stTrans.is_imported']
        )->where('stJob.job_type_id=5');

        $this->getSelect()->joinLeft(
            ['b'=> $subquery],
            'b.entity_id=`main_table`.block_id',
            []
        )->group('block_id');

        return $this;
    }

    public function getSelectCountSql()
    {

        $this->_renderFilters();
        $countSelect = clone $this->getSelect();
        $countSelect->reset();

        $select = clone $this->getSelect();
        $select->reset(Select::ORDER);
        $select->reset(Select::LIMIT_COUNT);
        $select->reset(Select::LIMIT_OFFSET);
        $countSelect->from(
            ['s' => $select]
        );
        $countSelect->reset(Select::COLUMNS);
        $countSelect->reset(Select::HAVING);
        $countSelect->columns(new \Zend_Db_Expr(("COUNT(DISTINCT block_id)")));

        return $countSelect;

    }

}