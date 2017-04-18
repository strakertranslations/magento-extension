<?php
class StrakerTranslations_EasyTranslationPlatform_Block_Adminhtml_Template_Grid_Renderer_Language
    extends Mage_Adminhtml_Block_Widget_Grid_Column_Renderer_Abstract
{
    public function render(Varien_Object $row)
    {
        $index = $this->getColumn()->getIndex();
        $languageCode = $row->getData($index);
        /** @var  $helper StrakerTranslations_EasyTranslationPlatform_Model_Api */
        $helper = Mage::getModel('strakertranslations_easytranslationplatform/api');
        return $helper->_getLanguageName($languageCode);
    }
}
