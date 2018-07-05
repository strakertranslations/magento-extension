<?php
class StrakerTranslations_EasyTranslationPlatform_Block_Adminhtml_Template_Grid_Renderer_StrakerTranslated
    extends Mage_Adminhtml_Block_Widget_Grid_Column_Renderer_Abstract
{
    public function render(Varien_Object $row)
    {
        if($row->getStrakerTranslated()){
            return Mage::helper('strakertranslations_easytranslationplatform')->__('Yes');
        }
        else{
            return Mage::helper('strakertranslations_easytranslationplatform')->__('No');
        }
    }
}