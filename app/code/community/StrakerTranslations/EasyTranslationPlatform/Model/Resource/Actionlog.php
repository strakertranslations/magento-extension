<?php
/**
 * Created by PhpStorm.
 * User: WlliamZhao
 * Date: 30/09/15
 * Time: 4:06 PM
 */ 
class StrakerTranslations_EasyTranslationPlatform_Model_Resource_Actionlog extends Mage_Core_Model_Resource_Db_Abstract
{

    protected function _construct()
    {
        $this->_init('strakertranslations_easytranslationplatform/actionlog', 'id');
    }

}