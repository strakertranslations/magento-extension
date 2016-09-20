<?php
class StrakerTranslations_EasyTranslationPlatform_Block_Adminhtml_New_Type extends Mage_Adminhtml_Block_Widget_Container{

    protected $_languages;

    protected function _beforeToHtml(){
        $this->setTemplate('straker/new/type.phtml');
    }

    public function renderTypes(){
        $storeId = $this->getRequest()->getParam('store');
        $types = Mage::getModel('strakertranslations_easytranslationplatform/job_type')->getCollection();
        $html = '';
        foreach ($types as $type){
            $html .= '<div><a class="job-type-btn" href="' . Mage::helper("adminhtml")->getUrl("adminhtml/straker_".str_replace(' ','_',strtolower($type->getTypeName()))."/new", array("store" => $storeId)) . '">' . $type->getTypeName() . '</a></div>';

        }
        return $html;
    }
}
