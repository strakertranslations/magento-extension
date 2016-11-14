<?php
class StrakerTranslations_EasyTranslationPlatform_Block_Adminhtml_New_Products_Attribute extends Mage_Adminhtml_Block_Widget_Container{

    private static  $_excludeAttributes = array(
        'name',
        'short_description',
        'description',
        'meta_title',
        'meta_description',
        'meta_keyword',
        'image_label',
        'small_image_label',
        'thumbnail_label',
        'url_path',
        'custom_layout_update',
        'email_template',
        'url_key'
    );

    protected function _beforeToHtml(){
        $this->setTemplate('straker/new/products/attributes.phtml');
    }

    public function getAttributes(){
        $attributes = Mage::getResourceModel('eav/entity_attribute_collection')
            ->setEntityTypeFilter(4)
            ->addFieldToFilter('backend_type', array('in' => array('varchar', 'text')))
            ->setFrontendInputTypeFilter(array('in' => array('text', 'textarea')))
            ->addFieldToFilter('attribute_code', array('nin' => self::$_excludeAttributes));
        return $attributes;
    }

}