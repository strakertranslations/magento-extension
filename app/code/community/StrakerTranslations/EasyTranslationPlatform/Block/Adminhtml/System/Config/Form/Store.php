<?php
class StrakerTranslations_EasyTranslationPlatform_Block_Adminhtml_System_Config_Form_Store extends Mage_Adminhtml_Block_System_Config_Form_Field
{
    /*
     * Set template
     */
    protected function _construct()
    {
        parent::_construct();
        $this->setTemplate('straker/system/config/store.phtml');
    }

    /**
     * Return element html
     *
     * @param  Varien_Data_Form_Element_Abstract $element
     * @return string
     */
    protected function _getElementHtml(Varien_Data_Form_Element_Abstract $element)
    {
        return $this->_toHtml();
    }

    /**
     * Generate button html
     *
     * @return string
     */
    public function getClearStoreButtonHtml($store)
    {
        if ($store->getId() && Mage::helper('strakertranslations_easytranslationplatform')->getStoreSetup($store->getId())) {
            $button = $this->getLayout()->createBlock('adminhtml/widget_button')
                ->setData(array(
                    'id' => 'straker_clear_store_button_' . $store->getCode(),
                    'label' => $this->helper('adminhtml')->__('Clear'),
                    'onclick' => 'setLocation(\'' . Mage::helper('adminhtml')->getUrl(
                            'adminhtml/straker_new/resetStoreSettings',
                            array('store' => $store->getId())
                        ) . '\');'
                ));
            return $button->toHtml();
        }
        else{

            return '<div class="empty-button">'.$this->__('No language settings applied').'</div>';
        }
    }
}