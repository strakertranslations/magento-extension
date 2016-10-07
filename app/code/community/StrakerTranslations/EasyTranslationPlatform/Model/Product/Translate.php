<?php
/**
 * Created by PhpStorm.
 * User: WlliamZhao
 * Date: 30/09/15
 * Time: 8:46 PM
 */ 
class StrakerTranslations_EasyTranslationPlatform_Model_Product_Translate extends Mage_Core_Model_Abstract
{

    protected function _construct()
    {
        $this->_init('strakertranslations_easytranslationplatform/product_translate');
    }

    public function importTranslation(){

        //if translated value is null, skip
        $translatedValue = $this->getTranslate();
        if (is_null($translatedValue)){
            return;
        }

        $product = Mage::getModel('catalog/product')->setStoreId($this->getStoreId())->load($this->getProductId());

        $productAttributeCode = $this->_getAttributeCode($this->getAttributeId());

        $this->setBackup($product->getData($productAttributeCode));

        $product->setData($productAttributeCode, $translatedValue)
            ->getResource()
            ->saveAttribute($product, $productAttributeCode);
        $this->setIsImported(1)->save();

        $product->clearInstance();

    }

    protected function _getAttributeCode($attributeId){

        if (!Mage::registry('attributeCodeCache_'.$attributeId)){
            $productAttributeCode = Mage::getModel('eav/entity_attribute')->load($attributeId)->getAttributeCode();
            Mage::register('attributeCodeCache_'.$attributeId,$productAttributeCode);
        }else{
            $productAttributeCode = Mage::registry('attributeCodeCache_'.$attributeId);
        }
        return $productAttributeCode;

    }

}