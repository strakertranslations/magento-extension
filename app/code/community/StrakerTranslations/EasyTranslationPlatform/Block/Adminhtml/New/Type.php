<?php
class StrakerTranslations_EasyTranslationPlatform_Block_Adminhtml_New_Type extends Mage_Adminhtml_Block_Widget_Container
{

    protected $_languages;

    protected function _beforeToHtml()
    {
        $this->setTemplate('straker/new/type.phtml');
    }

    public function renderTypes() 
    {
        $params = $this->getRequest()->getParams();
        $storeId = $params['store'];
        $sourceStoreId = array_key_exists('source_store_id', $params) ? $params['source_store_id'] : $this->getSourceStoreId();

        $types = Mage::getModel('strakertranslations_easytranslationplatform/job_type')->getCollection();
        $html = '';
        $title = '';
        foreach ($types as $type) {
            $typeName = $type->getTypeName();

            if ('Product' == $typeName) {
                $title = Mage::helper('strakertranslations_easytranslationplatform')->__('Select all your products or specific products you wish to translate, you can filter by product type, SKU, name, etc');
            } elseif ('Attribute' == $typeName) {
                $title = Mage::helper('strakertranslations_easytranslationplatform')->__('Select all attributes or specific ones');
            } elseif ('Category' == $typeName) {
                $title = Mage::helper('strakertranslations_easytranslationplatform')->__('Select all categories or specific ones');
            } elseif ('CMS Page' == $typeName) {
                $title = Mage::helper('strakertranslations_easytranslationplatform')->__('You can select which items from the CMS page to include/exclude: Title, Meta Keywords, Meta Description, Content Heading, Content');
            } elseif ('CMS Block' == $typeName) {
                $title = Mage::helper('strakertranslations_easytranslationplatform')->__('You can include/exclude the Title and Content');
            }

            $html .= '<div class="strakertranslations-adminhtml-job-type">
                        <a class="job-type-btn" href="' . Mage::helper("adminhtml")->getUrl("adminhtml/straker_".str_replace(' ', '_', strtolower($type->getTypeName()))."/new", array("store" => $storeId, 'source_store_id' => $sourceStoreId)) . '">' . $type->getTypeName() . '</a></div> <span class="st-tooltip"><img width="18px" src="/skin/adminhtml/default/straker/images/help.svg" /><span class="st-tooltiptext">' . $title . '</span></span>';
        }

        return $html;
    }
}
