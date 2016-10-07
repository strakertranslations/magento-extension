<?php
class StrakerTranslations_EasyTranslationPlatform_Block_Adminhtml_New_Cms_Block_Attribute extends Mage_Adminhtml_Block_Widget_Container{

    private static $_cmsBlockAttributes = array(
        'title' => 'Title',
        'content' => 'Content',
    );

    protected function _beforeToHtml(){
        $this->setTemplate('straker/new/cms/block/attributes.phtml');
    }

    public function getAttributes(){
        return self::$_cmsBlockAttributes;
    }

}