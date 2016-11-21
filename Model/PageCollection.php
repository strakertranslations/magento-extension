<?php
/**
 * Created by PhpStorm.
 * User: rakeshmistry
 * Date: 17/11/16
 * Time: 11:56 AM
 */

namespace Straker\EasyTranslationPlatform\Model;

use Magento\Framework\DB\Select;

class PageCollection extends \Magento\Cms\Model\ResourceModel\Page\Collection
{

    public function is_translated($target_store_id=1)
    {

        $strakerJobs = $this->_resource->getTable('straker_job');

        $strakerTrans = $this->_resource->getTable('straker_attribute_translation');

        $this->getSelect()->columns(
            'IF(max(stTrans.is_imported) IS NULL, 0, 1) as is_translated'
        )->joinLeft(
            ['stTrans' => $strakerTrans],
            'main_table.page_id=stTrans.entity_id',
            []
        );

        $this->getSelect()->columns(
            'stJob.job_id'
        )->joinLeft(
            ['stJob' => $strakerJobs],
            'stTrans.job_id=stJob.job_id and stJob.target_store_id='.$target_store_id.' and stJob.job_type_id=4',
            []
        )->group('page_id');

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
        $countSelect->reset(Select::GROUP);
        $group = $this->getSelect()->getPart(Select::GROUP);
        $countSelect->columns(new \Zend_Db_Expr(("COUNT(DISTINCT " . implode(", ", $group) . ")")));

        return $countSelect;

    }
}