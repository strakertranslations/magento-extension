<?php
class StrakerTranslations_EasyTranslationPlatform_Block_Adminhtml_Template_Grid_Renderer_Backend
    extends Mage_Adminhtml_Block_Widget_Grid_Column_Renderer_Abstract
{
    public function render(Varien_Object $row){
        $product = Mage::getModel('catalog/product')->load($row->getProductId());
        $url = $this->getUrl('adminhtml/catalog_product/edit', array(
            'store'=> Mage::getModel('strakertranslations_easytranslationplatform/job')->load($row->getjobId())->getStoreId(),
            'id'=>$product->getId())
        );
        $html = '<a target="_blank" href="'.$url.'">'.$this->__('View Product in Backend').'</a>';
        return $html;
    }

}
