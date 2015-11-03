<?php
/**
 * Created by PhpStorm.
 * User: WlliamZhao
 * Date: 30/09/15
 * Time: 8:46 PM
 */ 
class StrakerTranslations_EasyTranslationPlatform_Model_Resource_Product_Translate_Collection extends Mage_Core_Model_Resource_Db_Collection_Abstract
{

    protected function _construct()
    {
        $this->_init('strakertranslations_easytranslationplatform/product_translate');
    }

}