<?php
/**
 * Created by PhpStorm.
 * User: WlliamZhao
 * Date: 12/05/16
 * Time: 12:02 PM
 */ 
class StrakerTranslations_EasyTranslationPlatform_Model_Resource_Job_Cms_Page_Collection extends Mage_Core_Model_Resource_Db_Collection_Abstract
{

    protected function _construct()
    {
        $this->_init('strakertranslations_easytranslationplatform/job_cms_page');
    }

    public function getAllIds()
    {
        $idsSelect = clone $this->getSelect();
        $idsSelect->reset(Zend_Db_Select::ORDER);
        $idsSelect->reset(Zend_Db_Select::LIMIT_COUNT);
        $idsSelect->reset(Zend_Db_Select::LIMIT_OFFSET);
        $idsSelect->reset(Zend_Db_Select::COLUMNS);

        $idsSelect->columns('page_id', 'main_table');
        return $this->getConnection()->fetchCol($idsSelect);
    }

}