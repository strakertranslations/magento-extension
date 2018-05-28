<?php
class StrakerTranslations_EasyTranslationPlatform_Block_Adminhtml_Template_Grid_Renderer_Translated
    extends Mage_Adminhtml_Block_Widget_Grid_Column_Renderer_Abstract
{
    public function render(Varien_Object $row)
    {
        if($row->getVersion()){
            return Mage::helper('strakertranslations_easytranslationplatform')->__('Translated');
        }
        else{
            return Mage::helper('strakertranslations_easytranslationplatform')->__('Not Translated');
        }
    }
}
