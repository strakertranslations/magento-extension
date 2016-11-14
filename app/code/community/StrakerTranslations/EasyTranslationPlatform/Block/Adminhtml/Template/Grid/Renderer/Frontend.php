<?php
class StrakerTranslations_EasyTranslationPlatform_Block_Adminhtml_Template_Grid_Renderer_Frontend
    extends Mage_Adminhtml_Block_Widget_Grid_Column_Renderer_Abstract
{
    protected $_storeId;

    public function render(Varien_Object $row){
        $html = '';
        if(!$row->getVersion()){
            $html .= '<p class="inactive">' . $this->__('View Product in Frontend') . '</p>';
        }
        elseif ($row->getProductId()) {
            $product = Mage::getModel('catalog/product')->setStoreId($this->getStoreId($row))->load($row->getProductId());
            if (!$product->isDisabled() && $product->isVisibleInSiteVisibility()) {
                $html .= '<a target="_blank" href="' . $product->getProductUrl() . '">' . $this->__('View Product in Frontend') . '</a>';
            } else {
                $html .= 'Product is not visible in frontend';
            }
        }
        elseif($row->getCategoryId()){
            $category = Mage::getModel('catalog/category')->setStoreId($this->getStoreId($row))->load($row->getCategoryId());
            $html .= '<a target="_blank" href="' . $category->getUrl() . '">' . $this->__('View Category in Frontend') . '</a>';
        }
        return $html;
    }

    protected function getStoreId($row){
        if (!$this->_storeId){
            $this->_storeId = Mage::getModel('strakertranslations_easytranslationplatform/job')
                ->load($row->getjobId())
                ->getStoreId();
        }
        return $this->_storeId;
    }
}
