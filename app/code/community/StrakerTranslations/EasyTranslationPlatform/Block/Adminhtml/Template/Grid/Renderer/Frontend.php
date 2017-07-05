<?php
class StrakerTranslations_EasyTranslationPlatform_Block_Adminhtml_Template_Grid_Renderer_Frontend
    extends Mage_Adminhtml_Block_Widget_Grid_Column_Renderer_Abstract
{
    protected $_storeId;

    public function render(Varien_Object $row){
        $html = '';
        if(!$row->getVersion()){
            $html .= '<p class="inactive">' . $this->__('View in Frontend') . '</p>';
        }
        elseif ($row->getProductId()) {
            $product = Mage::getModel('catalog/product')->setStoreId($this->getStoreId($row))->load($row->getProductId());
            if (!$product->isDisabled() && $product->isVisibleInSiteVisibility()) {
                $html .= '<a target="_blank" href="' . $product->getProductUrl() . '">' . $this->__('View in Frontend') . '</a>';
            } else {
                $html .= 'Product is not visible in frontend';
            }
        }
        elseif($row->getPageId()){
            $url = '';
            if ($row->getPreviewUrl()) {
                $href = $row->getPreviewUrl();
            } else {
                $urlModel = Mage::getModel('core/url')->setStore($row->getData('_first_store_id'));
                $url = $urlModel->getUrl(
                    $row->getIdentifier(), array(
                        '_current' => false,
                        '_query'   => '___store=' . $this->getStoreCode($row),
                    )
                );
            }
            $html .= '<a target="_blank" href="' . $url . '">' . $this->__('View in Frontend') . '</a>';
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

    protected function getStoreCode($row){
        $this->getStoreId($row);
        return Mage::app()->getStore($this->_storeId)->getCode();
    }
}
