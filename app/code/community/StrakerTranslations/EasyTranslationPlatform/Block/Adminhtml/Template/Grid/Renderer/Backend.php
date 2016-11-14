<?php
class StrakerTranslations_EasyTranslationPlatform_Block_Adminhtml_Template_Grid_Renderer_Backend
    extends Mage_Adminhtml_Block_Widget_Grid_Column_Renderer_Abstract
{
    protected $_storeId;

    public function render(Varien_Object $row){
        $html = '';
        if(!$row->getVersion()){
            $html .= '<p class="inactive">' . $this->__('View Product in Backend') . '</p>';
        }
        elseif ($row->getProductId()) {
            $product = Mage::getModel('catalog/product')->load($row->getProductId());
            $url = $this->getUrl('adminhtml/catalog_product/edit', array(
                    'store' => $this->getStoreId($row),
                    'id' => $product->getId())
            );
            $html .= '<a target="_blank" href="' . $url . '">' . $this->__('View Product in Backend') . '</a>';
        }
        elseif($row->getCategoryId()) {
            $category = Mage::getModel('catalog/category')->load($row->getCategoryId());
            $url = $this->getUrl('adminhtml/catalog_category/edit', array(
                    'store' => $this->getStoreId($row),
                    'id' => $category->getId())
            );
            $html .= '<a target="_blank" href="' . $url . '">' . $this->__('View Category in Backend') . '</a>';
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
