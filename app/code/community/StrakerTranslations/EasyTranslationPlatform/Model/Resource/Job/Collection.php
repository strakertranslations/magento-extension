<?php
/**
 * Created by PhpStorm.
 * User: WlliamZhao
 * Date: 30/09/15
 * Time: 4:37 PM
 */ 
class StrakerTranslations_EasyTranslationPlatform_Model_Resource_Job_Collection extends Mage_Core_Model_Resource_Db_Collection_Abstract
{

    protected function _construct()
    {
        $this->_init('strakertranslations_easytranslationplatform/job');
    }

}