<?php
class StrakerTranslations_EasyTranslationPlatform_Block_Adminhtml_New_Setupstore extends Mage_Adminhtml_Block_Widget_Container{

    protected $_languages;

    protected function _beforeToHtml(){
        $this->setTemplate('straker/new/setupstore.phtml');
    }

    public function renderLanguageSelect($name){
        if (!$this->_languages) {
            $this->_languages = Mage::getModel('strakertranslations_easytranslationplatform/api', array('store' => $this->getSetupStoreId()))->getLanguages();
        }
        $html = '<select class="validate-select" name="'.$name.'">';
        $html .= '<option value="">' . $this->__('Select a language') . '</option>';
        if ($this->_languages) {
            foreach ($this->_languages as $language) {
                $html .= '<option value="' . $language->code . '">' . $language->name . '</option>';
            }
        }
        $html .= '</select>';
        return $html;
    }
}