<?php
/**
 * Created by PhpStorm.
 * User: WlliamZhao
 * Date: 30/09/15
 * Time: 8:41 PM
 */ 
class StrakerTranslations_EasyTranslationPlatform_Model_Job_Type extends Mage_Core_Model_Abstract
{

    protected function _construct()
    {
        $this->_init('strakertranslations_easytranslationplatform/job_type');
    }

    public function getOptionArray(){
        $data = [];
        foreach($this->getCollection()->getData() as $d){
            $data[$d['type_id']] = $d['type_name'];
        }
        return $data;
    }
}