<?php
/**
 * Created by PhpStorm.
 * User: WlliamZhao
 * Date: 30/09/15
 * Time: 8:44 PM
 */ 
class StrakerTranslations_EasyTranslationPlatform_Model_Resource_Product_Attributes extends Mage_Core_Model_Resource_Db_Abstract
{

    protected function _construct()
    {
        $this->_init('strakertranslations_easytranslationplatform/product_attributes', 'id');
    }

}