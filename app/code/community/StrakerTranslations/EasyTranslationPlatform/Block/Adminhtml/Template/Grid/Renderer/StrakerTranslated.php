<?php
class StrakerTranslations_EasyTranslationPlatform_Block_Adminhtml_Template_Grid_Renderer_StrakerTranslated
    extends Mage_Adminhtml_Block_Widget_Grid_Column_Renderer_Abstract
{
    public function render(Varien_Object $row)
    {
        if($row->getStrakerTranslated()){
            return 'Yes';
        }
        else{
            return 'No';
        }
    }
}