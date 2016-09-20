<?php
class StrakerTranslations_EasyTranslationPlatform_Block_Adminhtml_New_Cms_Page_Attribute extends Mage_Adminhtml_Block_Widget_Container{

    private static $_cmsPageAttributes = array(
        'title' => 'Title',
        'meta_keywords' => 'Meta Keywords',
        'meta_description' => 'Meta Description',
        'content_heading' => 'Content Heading',
        'content' => 'Content',
    );

    protected function _beforeToHtml(){
        $this->setTemplate('straker/new/cms/page/attributes.phtml');
    }

    public function getAttributes(){
        return self::$_cmsPageAttributes;
    }

}