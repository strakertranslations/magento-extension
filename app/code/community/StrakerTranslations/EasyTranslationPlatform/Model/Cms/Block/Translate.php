<?php
/**
 * Created by PhpStorm.
 * User: WlliamZhao
 * Date: 12/05/16
 * Time: 12:07 PM
 */ 
class StrakerTranslations_EasyTranslationPlatform_Model_Cms_Block_Translate extends Mage_Core_Model_Abstract
{

    protected function _construct()
    {
        $this->_init('strakertranslations_easytranslationplatform/cms_block_translate');
    }

    public function importTranslation(){

        $newEntityId =Mage::getModel('strakertranslations_easytranslationplatform/job_cms_block')->load($this->getJobCmsId())
          ->getNewEntityId();


        if ($this->getTranslate()){

            $writeConnection = $this->_getConnection();

            $query = 'UPDATE `'.Mage::getSingleton('core/resource')->getTableName('cms/block')
              . '` SET '.$this->getColumnName() .' = \''.addslashes($this->getTranslate()).' \' WHERE block_id = '.$newEntityId;


            $writeConnection->query($query);


        }

        $this->setIsImported(1)->save();

    }

    private function _getConnection() {
        return Mage::getSingleton('core/resource')->getConnection('core_write');
    }

}