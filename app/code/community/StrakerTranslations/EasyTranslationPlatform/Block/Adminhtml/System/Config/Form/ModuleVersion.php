<?php
/**
 * Created by PhpStorm.
 * User: Paul
 * Date: 24/04/18
 * Time: 10:01
 */


class StrakerTranslations_EasyTranslationPlatform_Block_Adminhtml_System_Config_Form_ModuleVersion extends Mage_Adminhtml_Block_System_Config_Form_Field
{
    protected function _getElementHtml(Varien_Data_Form_Element_Abstract $element)
    {
        $helper = Mage::helper('strakertranslations_easytranslationplatform');
        $moduleVersion = $helper->getModuleVersion();
//        $format = Mage::app()->getLocale()->getDateTimeFormat(Mage_Core_Model_Locale::FORMAT_TYPE_MEDIUM);
//        return Mage::app()->getLocale()->date(intval($element->getValue()))->toString($format);
        return $moduleVersion;
    }
}
