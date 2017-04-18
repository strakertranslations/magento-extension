<?php
class StrakerTranslations_EasyTranslationPlatform_Block_Adminhtml_Template_Grid_Renderer_PageGridAction
    extends Mage_Adminhtml_Block_Widget_Grid_Column_Renderer_Action
//    extends Mage_Adminhtml_Block_Widget_Grid_Column_Renderer_Abstract
{
    public function render(Varien_Object $row)
    {
//        $actions = [];
        Mage::dispatchEvent('adminhtml_cms_page_grid_renderer_action_before_render', array('row' => $row));
        if ($row->getPreviewUrl()) {
            $href = $row->getPreviewUrl();
        } else {
            $urlModel = Mage::getModel('core/url')->setStore($row->getData('_first_store_id'));
            $href = $urlModel->getUrl(
                $row->getIdentifier(), array(
                    '_current' => false,
                    '_query'   => '___store=' . $row->getStoreCode(),
                )
            );
        }

//        $actions[] = [
//            'url' => $href,
//            'target' => '_blank',
//            'caption' => Mage::helper('newsletter')->__('Preview')
//        ];

        $actions[] = [
            'url' => $this->getUrl('*/*/removeFromCart', ['page_id' => $row->getPageId()] ),
            'caption' => Mage::helper('newsletter')->__('Remove')
        ];

        $this->getColumn()->setActions($actions);
        return parent::render($row);
//        return '<a href="' . $href . '" target="_blank">' . $this->__('Preview') . '</a>';
    }
}