<?php
class StrakerTranslations_EasyTranslationPlatform_Block_Adminhtml_New_Cms_Page_Confirm extends Mage_Adminhtml_Block_Widget_Container
{
    /**
     * Set template
     */
    public function __construct()
    {
        parent::__construct();
        $this->setTemplate('straker/new/cms/page/confirm.phtml');
    }


    protected function _prepareLayout()
    {
        $this->_addButton('submit', array(
            'label'   => Mage::helper('catalog')->__('Submit'),
            'onclick' => "$('submit-new-job-form').submit();",
            'class'   => 'task'
        ));

        $this->setChild('grid', $this->getLayout()->createBlock('strakertranslations_easytranslationplatform/adminhtml_new_cms_page_confirm_grid', 'cms_page.grid', array('store' => $this->getStore(), 'cms_page' => $this->getCmsPage(), 'attr' => $this->getAttr())));
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

    /**
     * Check whether it is single store mode
     *
     * @return bool
     */
    public function isSingleStoreMode()
    {
        if (!Mage::app()->isSingleStoreMode()) {
            return false;
        }
        return true;
    }
}
