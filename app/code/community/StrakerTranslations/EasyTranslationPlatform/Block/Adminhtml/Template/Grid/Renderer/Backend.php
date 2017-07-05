<?php
class StrakerTranslations_EasyTranslationPlatform_Block_Adminhtml_Template_Grid_Renderer_Backend
    extends Mage_Adminhtml_Block_Widget_Grid_Column_Renderer_Abstract
{
    protected $_storeId;

    public function render(Varien_Object $row){
        $html = '';
        if(!$row->getVersion()){
            $html .= '<p class="inactive">' . $this->__('View in Backend') . '</p>';
        }
        elseif ($row->getProductId()) {
            $product = Mage::getModel('catalog/product')->load($row->getProductId());
            $url = $this->getUrl('adminhtml/catalog_product/edit', array(
                    'store' => $this->getStoreId($row),
                    'id' => $product->getId()
                )
            );
            $html .= '<a target="_blank" href="' . $url . '">' . $this->__('View in Backend') . '</a>';
        }elseif($row->getAttributeId()){
            $product = Mage::getModel('catalog/product')->load($row->getAttributeId());
            $url = $this->getUrl('adminhtml/catalog_product_attribute/edit', array(
                    'attribute_id' => $row->getAttributeId()
                )
            );
            $html .= '<a target="_blank" href="' . $url . '">' . $this->__('View in Backend') . '</a>';
        }
        elseif($row->getCategoryId()) {
            $category = Mage::getModel('catalog/category')->load($row->getCategoryId());
            $url = $this->getUrl('adminhtml/catalog_category/edit', array(
                    'store' => $this->getStoreId($row),
                    'id' => $category->getId()
                )
            );
            $html .= '<a target="_blank" href="' . $url . '">' . $this->__('View in Backend') . '</a>';
        }elseif($row->getPageId()){
            $url = Mage::helper("adminhtml")->getUrl("adminhtml/cms_page/edit",array("page_id"=>$row->getNewEntityId()));
            $html .= '<a target="_blank" href="' . $url . '">' . $this->__('View in Backend') . '</a>';
        }elseif($row->getBlockId()){
            $url = Mage::helper("adminhtml")->getUrl("adminhtml/cms_block/edit",array("block_id"=>$row->getNewEntityId()));
            $html .= '<a target="_blank" href="' . $url . '">' . $this->__('View in Backend') . '</a>';
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
