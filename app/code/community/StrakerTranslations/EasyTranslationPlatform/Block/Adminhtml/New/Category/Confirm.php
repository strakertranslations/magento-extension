<?php
class StrakerTranslations_EasyTranslationPlatform_Block_Adminhtml_New_Category_Confirm extends Mage_Adminhtml_Block_Widget_Container{
    /**
     * Set template
     */
    public function __construct()
    {
        parent::__construct();
        $this->setTemplate('straker/new/category/confirm.phtml');
    }

    /**
     * Prepare button and grid
     *
     * @return Mage_Adminhtml_Block_Catalog_Product
     */
    protected function _prepareLayout()
    {
        $this->_addButton('submit', array(
            'label'   => Mage::helper('catalog')->__('Submit'),
            'onclick' => "$('submit-new-job-form').submit();",
            'class'   => 'task'
        ));

        $this->setChild('grid', $this->getLayout()->createBlock('strakertranslations_easytranslationplatform/adminhtml_new_category_confirm_grid', 'category.grid', array('store' => $this->getStore(), 'attr' => $this->getAttr(), 'category' => $this->getCategory())));
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
