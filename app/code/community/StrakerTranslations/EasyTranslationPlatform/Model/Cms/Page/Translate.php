<?php
/**
 * Created by PhpStorm.
 * User: WlliamZhao
 * Date: 12/05/16
 * Time: 12:06 PM
 */ 
class StrakerTranslations_EasyTranslationPlatform_Model_Cms_Page_Translate extends Mage_Core_Model_Abstract
{

    protected function _construct()
    {
        $this->_init('strakertranslations_easytranslationplatform/cms_page_translate');
    }

    public function importTranslation(){

        $newEntityId =Mage::getModel('strakertranslations_easytranslationplatform/job_cms_page')->load($this->getJobCmsId())
          ->getNewEntityId();


        if ($this->getTranslate()){

            $writeConnection = $this->_getConnection();

            $query = 'UPDATE `'.Mage::getSingleton('core/resource')->getTableName('cms/page')
              . '` SET '.$this->getColumnName() .' = \''.addslashes($this->getTranslate()).' \' WHERE page_id = '.$newEntityId;


            $writeConnection->query($query);


        }

        $this->setIsImported(1)->save();

    }

    private function _getConnection() {
        return Mage::getSingleton('core/resource')->getConnection('core_write');
    }

}