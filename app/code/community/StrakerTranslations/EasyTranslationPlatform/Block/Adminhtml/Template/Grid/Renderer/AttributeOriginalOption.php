<?php
class StrakerTranslations_EasyTranslationPlatform_Block_Adminhtml_Template_Grid_Renderer_AttributeOriginalOption
    extends Mage_Adminhtml_Block_Widget_Grid_Column_Renderer_Abstract
{

    public function render(Varien_Object $row)
    {
        $original = $this->xml2array(simplexml_load_string($row->getOriginal()));

        return !empty($original['option']) ? implode(', ', $original['option']) : Mage::helper('strakertranslations_easytranslationplatform')->__('N/A');
    }

    public function xml2array( $xmlObject, $out = array () )
    {
        foreach ((array) $xmlObject as $index => $node)
            $out[$index] = ( is_object($node) ) ? $this->xml2array($node) : $node;

        return $out;
    }
}
