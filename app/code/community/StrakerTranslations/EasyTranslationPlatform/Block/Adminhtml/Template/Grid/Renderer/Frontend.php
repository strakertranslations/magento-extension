<?php
class StrakerTranslations_EasyTranslationPlatform_Block_Adminhtml_Template_Grid_Renderer_Frontend
    extends Mage_Adminhtml_Block_Widget_Grid_Column_Renderer_Abstract
{
    protected $_storeId;

    public function render(Varien_Object $row)
    {
        $html = '';
        if(!$row->getVersion()){
            $html .= '<p class="inactive">' . Mage::helper('strakertranslations_easytranslationplatform')->__('View in Frontend') . '</p>';
        }
        elseif ($row->getProductId()) {
            $product = Mage::getModel('catalog/product')->setStoreId($this->getStoreId($row))->load($row->getProductId());
            if (!$product->isDisabled() && $product->isVisibleInSiteVisibility()) {
                $html .= '<a target="_blank" href="' . $product->getProductUrl() . '">' . Mage::helper('strakertranslations_easytranslationplatform')->__('View in Frontend') . '</a>';
            } else {

                $html .= Mage::helper('strakertranslations_easytranslationplatform')->__('Product is not visible in frontend');
            }
        }
        elseif($row->getPageId()){
            //current page_id is original page id, so need update with new page id
            if ($row->getNewEntityId()) $row->setPageId($row->getNewEntityId());
            //set preview url using event observer, $row must have or only need a correct page_id
            Mage::dispatchEvent('adminhtml_cms_page_grid_renderer_action_before_render', array('row' => $row));

            if ($row->getPreviewUrl()) {
                $url = $row->getPreviewUrl();
            } else {
                $urlModel = Mage::getModel('core/url')->setStore($this->getStoreId($row));
                $url = $urlModel->getUrl(
                    $row->getIdentifier(), array(
                        '_current' => false,
                        '_query'   => '___store=' . $this->getStoreCode($row),
                    )
                );
            }

            $html .= '<a target="_blank" href="' . $url . '">' . Mage::helper('strakertranslations_easytranslationplatform')->__('View in Frontend') . '</a>';
        }

        return $html;
    }

    protected function getStoreId($row)
    {
        if (!$this->_storeId){
            $this->_storeId = Mage::getModel('strakertranslations_easytranslationplatform/job')
                ->load($row->getjobId())
                ->getStoreId();
        }

        return $this->_storeId;
    }

    protected function getStoreCode($row)
    {
        $this->getStoreId($row);
        return Mage::app()->getStore($this->_storeId)->getCode();
    }
}
