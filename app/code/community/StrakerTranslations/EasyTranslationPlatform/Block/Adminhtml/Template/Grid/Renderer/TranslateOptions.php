<?php
class StrakerTranslations_EasyTranslationPlatform_Block_Adminhtml_Template_Grid_Renderer_TranslateOptions
    extends Mage_Adminhtml_Block_Widget_Grid_Column_Renderer_Abstract
{
    protected $_storeId;

    public function render(Varien_Object $row){
        if ( in_array($row->getFrontendInput(), ['select', 'multiselect'] ) ) {
            $html = '<input type="checkbox" name="option" value="' . $row->getAttributeId() . '" class="checkbox-option">';
        }
        else{
            $html = 'N/A';
        }
        return $html;
    }

}
