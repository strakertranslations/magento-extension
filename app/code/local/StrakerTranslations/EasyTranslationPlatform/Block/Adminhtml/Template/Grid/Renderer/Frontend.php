<?php
class StrakerTranslations_EasyTranslationPlatform_Block_Adminhtml_Template_Grid_Renderer_Frontend
    extends Mage_Adminhtml_Block_Widget_Grid_Column_Renderer_Abstract
{
    public function render(Varien_Object $row){
        $product = Mage::getModel('catalog/product')->load($row->getProductId());
        if (!$product->isDisabled() && $product->isVisibleInSiteVisibility()){
            $html = '<a target="_blank" href="'.$product->getProductUrl().'">'.$this->__('View Product in Frontend').'</a>';
        }
        else{
            $html = 'Product is not visible in frontend';
        }
        return $html;
    }

}
