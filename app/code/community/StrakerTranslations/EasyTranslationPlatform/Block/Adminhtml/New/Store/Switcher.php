<?php
/**
 * Created by PhpStorm.
 * User: Paul
 * Date: 8/06/18
 * Time: 09:38
 */

class StrakerTranslations_EasyTranslationPlatform_Block_Adminhtml_New_Store_Switcher extends  Mage_Adminhtml_Block_Store_Switcher
{
    public function __construct()
    {
        parent::__construct();
        $this->setTemplate('straker/new/store/switcher.phtml');
        $this->setUseConfirm(false);
        $this->setUseAjax(true);
        $this->setDefaultStoreName($this->__('All Store Views'));
    }

    protected $_storeVarName = 'source_store_id';

    public function getDestinationStore(){
        $destinationStoreId = $this->getRequest()->getParam('store', Mage_Catalog_Model_Abstract::DEFAULT_STORE_ID);
        return Mage::app()->getStore($destinationStoreId);
    }
}