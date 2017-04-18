<?php
/**
 * Created by PhpStorm.
 * User: WlliamZhao
 * Date: 18/01/16
 * Time: 7:41 PM
 */ 
class StrakerTranslations_EasyTranslationPlatform_Model_Attribute_Translate extends Mage_Core_Model_Abstract
{

    protected function _construct()
    {
        $this->_init('strakertranslations_easytranslationplatform/attribute_translate');
    }

    public function importTranslation(){
//        if ($this->getTranslate()){
//
//            $dataInJson =  json_encode(simplexml_load_string($this->getTranslate()));
//            $data = json_decode($dataInJson,true);
//
//            $writeConnection = $this->_getConnection();
//            $prefix = Mage::getConfig()->getTablePrefix()->__toString();
//            $storeId = (int) $this->getStoreId();
//
//            foreach ($data as $k => $attribute ){
//                if ($k == 'title' && $attribute){
//
//                    $attributeId = (int) $this->getAttributeId();
//
//                    $writeConnection->query("
//                    INSERT INTO `" .  $prefix . "eav_attribute_label` ( `attribute_id`, `store_id`, `value`)
//                    SELECT {$attributeId}, {$storeId}, '{$attribute}'
//                    FROM (select 1) as a
//                    WHERE NOT EXISTS(
//                         select `attribute_label_id`
//                         from `" .  $prefix . "eav_attribute_label`
//                         where `attribute_id`={$attributeId} and `store_id` ={$storeId});");
//
//                    $writeConnection->query("
//                    UPDATE `" .  $prefix . "eav_attribute_label` SET `value` = '{$attribute}'
//                    where `attribute_id`={$attributeId} and `store_id` ={$storeId} limit 1;");
//
//                }
//
//                if ($k = 'option'){
//                    foreach ($attribute as $optionId => $optionValue) {
//
//                        $optionId = str_replace('id_', '',$optionId);
//
//                        $writeConnection->query("
//                    INSERT INTO `" .  $prefix . "eav_attribute_option_value` ( `option_id`, `store_id`, `value`)
//                    SELECT {$optionId}, {$storeId}, '{$optionValue}'
//                    FROM (select 1) as a
//                    WHERE NOT EXISTS(
//                         select `value_id`
//                         from `" .  $prefix . "eav_attribute_option_value`
//                         where `option_id`={$optionId} and `store_id` ={$storeId});");
//
//
//                        $writeConnection->query("
//                    UPDATE `" .  $prefix . "eav_attribute_option_value` SET `value` = '{$optionValue}'
//                    where `option_id`={$optionId} and `store_id` ={$storeId} limit 1;");
//
//                    }
//
//                }
//
//            }
//
//
//        }

        if ($this->getTranslate()){
            $dataInJson =  json_encode(simplexml_load_string($this->getTranslate()));
            $data = json_decode($dataInJson,true);
            $storeId = (int) $this->getStoreId();
            /** @var Mage_Eav_Model_Attribute $eavAttributeModel */
            $eavAttributeModel = Mage::getModel('eav/entity_attribute');
            foreach ($data as $k => $attribute ){
                if ($k == 'title' && $attribute){
                    $attributeId = (int) $this->getAttributeId();
                    $eavAttributeModel->load($attributeId);
                    if(!empty($eavAttributeModel->getId())){
                        $storeLabels = $eavAttributeModel->getStoreLabels();
                        if(is_array($storeLabels)){
                            //get all current stores
                            $stores = array_keys($storeLabels);
                            if(!in_array($storeId, $stores)){
                                $storeLabels[$storeId] = trim($attribute);
                                $eavAttributeModel->setData('store_labels', $storeLabels)->save();
                            }
                        }
                    }
                }

                if ($k == 'option' && $attribute){
                    $setup = new Mage_Eav_Model_Entity_Setup('core_setup');

                    /* @var $model Mage_Catalog_Model_Entity_Attribute */
                    $attrModel = Mage::getModel('catalog/resource_eav_attribute');
                    $attrModel->load($this->getAttributeId());

                    /** @var $valuesCollection Mage_Eav_Model_Resource_Entity_Attribute_Option_Collection */
                    $valuesCollection = Mage::getResourceModel('eav/entity_attribute_option_collection')
                        ->setStoreFilter($storeId, false)
                        ->setAttributeFilter($attrModel->getId());

                    //0 => ['option_id' => '20', 'attribute_id' => '92', 'sort_order' => '0', 'value' => 'Black' ]
                    $optionData = $valuesCollection->getData();
                    $optionIds = array_column($optionData, 'option_id');

                    $optionValueTable = $setup->getTable('eav/attribute_option_value');

                    $newOptionData = [];
                    foreach ($attribute as $optionId => $optionValue) {
                        $optionId = str_replace('id_', '',$optionId);
//                        /** @var Mage_Eav_Model_Entity_Attribute_Option $optionModel */
//                        $optionModel = Mage::getModel('eav/entity_attribute_option');
//                        $optionModel->load($optionId);
                        if( !in_array($optionId, $optionIds)){
                            array_push(
                                $newOptionData,
                                [
                                    'option_id' => $optionId,
                                    'store_id' => $storeId,
                                    'value' => trim($optionValue)
                                ]
                            );
                        }
                    }
                    if($newOptionData){
                        $setup->getConnection()->insertMultiple($optionValueTable, $newOptionData);
                    }
                }
            }
        }
        $this->setIsImported(1)->save();
    }

//    private function _getConnection() {
//        return Mage::getSingleton('core/resource')->getConnection('core_write');
//    }

}