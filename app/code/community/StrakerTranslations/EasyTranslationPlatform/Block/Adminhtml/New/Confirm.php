<?php
class StrakerTranslations_EasyTranslationPlatform_Block_Adminhtml_New_Confirm extends Mage_Adminhtml_Block_Widget_Container
{
    /**
     * Set template
     */
    public function __construct()
    {
        parent::__construct();
        $this->setTemplate('straker/new/confirm.phtml');
    }

    /**
     * Prepare button and grid
     *
     * @return Mage_Adminhtml_Block_Catalog_Product
     */
    protected function _prepareLayout()
    {
        $this->_addButton(
            'submit', array(
            'label'   => $this->__('Submit'),
            'onclick' => "$('submit-new-job-form').submit();",
            //            'onclick' => "setLocation('{$this->getUrl('*/*/submit')}')",
            'class'   => 'task'
            )
        );

        $this->setChild('grid', $this->getLayout()->createBlock('strakertranslations_easytranslationplatform/adminhtml_new_confirm_grid', 'product.grid', array('store' => $this->getStore(), 'attr' => $this->getAttr(), 'product' => $this->getProduct())));
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
