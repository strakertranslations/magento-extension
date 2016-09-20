<?php
/**
 * Created by PhpStorm.
 * User: WlliamZhao
 * Date: 12/11/15
 * Time: 8:53 PM
 */ 
class StrakerTranslations_EasyTranslationPlatform_Model_Category_Translate extends Mage_Core_Model_Abstract
{

    protected function _construct()
    {
        $this->_init('strakertranslations_easytranslationplatform/category_translate');
    }

    public function importTranslation(){

        $category = Mage::getModel('catalog/category')->setStoreId($this->getStoreId())->load($this->getCategoryId());

        $categoryAttributeCode = $this->_getAttributeCode($this->getAttributeId());

        $this->setBackup($category->getData($categoryAttributeCode));

        $category->setData($categoryAttributeCode, $this->getTranslate())
          ->getResource()
          ->saveAttribute($category, $categoryAttributeCode);
        $this->setIsImported(1)->save();

        $category->clearInstance();

    }

    protected function _getAttributeCode($attributeId){

        if (!Mage::registry('attributeCodeCache_'.$attributeId)){
            $categoryAttributeCode = Mage::getModel('eav/entity_attribute')->load($attributeId)->getAttributeCode();
            Mage::register('attributeCodeCache_'.$attributeId,$categoryAttributeCode);
        }else{
            $categoryAttributeCode = Mage::registry('attributeCodeCache_'.$attributeId);
        }
        return $categoryAttributeCode;

    }

}