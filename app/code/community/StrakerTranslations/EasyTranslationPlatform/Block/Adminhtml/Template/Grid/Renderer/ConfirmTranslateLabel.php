<?php
class StrakerTranslations_EasyTranslationPlatform_Block_Adminhtml_Template_Grid_Renderer_ConfirmTranslateLabel
    extends Mage_Adminhtml_Block_Widget_Grid_Column_Renderer_Abstract
{
    protected $_storeId;

    protected $_label;

    public function render(Varien_Object $row){
        $id = $row->getAttributeId();
        $html = '<input disabled type="checkbox" name="option" value="' . $id . '" class="checkbox-option"';
        if (in_array($id, $this->_getLabel())) {
            $html .= ' checked="checked" >';
        }
        else{
            $html .= ' >';
        }
        return $html;
    }

    protected function _getLabel(){
        if(!$this->_label){
            $this->_label = Mage::getSingleton('adminhtml/session')->getData('straker_new_attribute');
        }
        return $this->_label;
    }

}
