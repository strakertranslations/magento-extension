<?php
class StrakerTranslations_EasyTranslationPlatform_Block_Adminhtml_System_Config_Form_RestoreButton extends Mage_Adminhtml_Block_System_Config_Form_Field
{
    /*
     * Set template
     */
    protected function _construct()
    {
        parent::_construct();
        $this->setTemplate('straker/system/config/button.phtml');
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
     * Return ajax url for button
     *
     * @return string
     */
    public function getAjaxCheckUrl()
    {
        return Mage::helper('adminhtml')->getUrl('adminhtml/adminhtml_atwixtweaks/check');
    }

    /**
     * Generate button html
     *
     * @return string
     */
    public function getButtonHtml()
    {
        $button = $this->getLayout()->createBlock('adminhtml/widget_button')
            ->setData(array(
                'id'        => 'straker_clear_button',
                'label'     => $this->helper('adminhtml')->__('Restore Product Data'),
                'onclick'   => 'setLocation(\''.Mage::helper('adminhtml')->getUrl('adminhtml/straker_new/restoreProdcutTable').'\');'
            ));

        return $button->toHtml();
    }
}