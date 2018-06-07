<?php
class StrakerTranslations_EasyTranslationPlatform_Block_Adminhtml_New_Products extends Mage_Adminhtml_Block_Widget_Container
{
    /**
     * Set template
     */
    public function __construct()
    {
        parent::__construct();
        $this->setTemplate('straker/new/products.phtml');
    }

    /**
     * Prepare button and grid
     *
     * @return Mage_Adminhtml_Block_Catalog_Product
     */
    protected function _prepareLayout()
    {
//        $this->_addButton('add_new', array(
//            'label'   => Mage::helper('strakertranslations_easytranslationplatform')->__('Add Product'),
//            'onclick' => "setLocation('{$this->getUrl('*/*/new')}')",
//            'class'   => 'add'
//        ));

        $this->setChild('grid', $this->getLayout()->createBlock('strakertranslations_easytranslationplatform/adminhtml_new_products_grid', 'product.grid', array('setup_store_id' => $this->getSetupStoreId(), 'attr' => $this->getAttr())));
        $this->setChild('store_switcher', $this->getLayout()->createBlock('strakertranslations_easytranslationplatform/adminhtml_new_products_store_switcher'));
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
