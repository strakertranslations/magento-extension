<?php
class StrakerTranslations_EasyTranslationPlatform_Block_Adminhtml_New_Category_Attribute extends Mage_Adminhtml_Block_Widget_Container{
    protected function _beforeToHtml(){
        $this->setTemplate('straker/new/category/attributes.phtml');
    }
}