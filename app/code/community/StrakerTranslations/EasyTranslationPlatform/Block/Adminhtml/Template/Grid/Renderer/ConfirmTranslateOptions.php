<?php
class StrakerTranslations_EasyTranslationPlatform_Block_Adminhtml_Template_Grid_Renderer_ConfirmTranslateOptions
    extends Mage_Adminhtml_Block_Widget_Grid_Column_Renderer_Abstract
{
    protected $_storeId;

    protected $_option;

    public function render(Varien_Object $row)
    {
        $id = $row->getAttributeId();
        if (in_array($row->getFrontendInput(), array('select', 'multiselect'))) {
            $html = '<input disabled type="checkbox" name="option" value="' . $id . '" class="checkbox-option"';
            if (in_array($id, $this->_getOption())) {
                $html .= ' checked="checked" >';
            }
            else{
                $html .= ' >';
            }
        }
        else{
            $html = Mage::helper('strakertranslations_easytranslationplatform')->__('N/A');
        }

        return $html;
    }

    protected function _getOption()
    {
        if(!$this->_option){
            $this->_option = explode(',', Mage::getSingleton('adminhtml/session')->getData('straker_new_option'));
        }

        return $this->_option;
    }

}
