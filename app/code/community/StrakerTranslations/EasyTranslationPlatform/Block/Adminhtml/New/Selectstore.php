<?php
class StrakerTranslations_EasyTranslationPlatform_Block_Adminhtml_New_Selectstore extends Mage_Adminhtml_Block_Widget_Container{
    protected function _beforeToHtml(){
        $this->setTemplate('straker/new/selectstore.phtml');
    }
}