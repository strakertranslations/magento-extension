<?php
class StrakerTranslations_EasyTranslationPlatform_Block_Adminhtml_New_Register extends Mage_Adminhtml_Block_Widget_Container{

    protected $_countires;

    protected function _beforeToHtml(){
        $this->setTemplate('straker/new/register.phtml');
    }

    public function renderWelcomeIframe(){
        // return '<div id="white-iframe" style="background: white; border: 5px solid black; min-height: 200px; padding:20px; margin-bottom: 20px;">
        //         <h3>First time iFrame provided by Straker</h3>
        //     </div>';
        return '<div class="magento-banner-wrap"><img src="/skin/adminhtml/default/straker/images/magento-banner.jpg"></div>';
    }

    public function renderCountriesSelect($name){
        if (!$this->_countires) {
            $this->_countires = Mage::getModel('strakertranslations_easytranslationplatform/api')->getCountries();
        }
        $html = '<select class="validate-select" name="'.$name.'">';
        $html .= '<option value="">' . $this->__('Select a country') . '</option>';
        if ($this->_countires) {
            foreach ($this->_countires as $country) {
                $html .= '<option value="' . $country->code . '">' . $country->name . '</option>';
            }
        }
        $html .= '</select>';
        return $html;
    }
}
