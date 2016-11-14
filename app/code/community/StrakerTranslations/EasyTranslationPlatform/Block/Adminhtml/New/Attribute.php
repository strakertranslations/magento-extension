<?php
class StrakerTranslations_EasyTranslationPlatform_Block_Adminhtml_New_Attribute extends Mage_Adminhtml_Block_Widget_Container{

    /**
     * Set template
     */
    public function __construct()
    {
        parent::__construct();
        $this->setTemplate('straker/new/attribute.phtml');
    }

    protected function _prepareLayout()
    {
        $this->setChild('grid', $this->getLayout()->createBlock('strakertranslations_easytranslationplatform/adminhtml_new_attribute_grid', 'attribute.grid', array('setup_store_id' => $this->getSetupStoreId() )));
        return parent::_prepareLayout();
    }


    /**
     * Render grid
     *
     * @return string
     */
    public function getGridHtml()
    {
        return $this->getChildHtml('grid');
    }
}