<?php
class StrakerTranslations_EasyTranslationPlatform_Block_Adminhtml_Template_Grid_Renderer_TextArea
    extends Mage_Adminhtml_Block_Widget_Grid_Column_Renderer_Abstract
{
    public function render(Varien_Object $row)
    {
        $index = $this->getColumn()->getIndex();
        if($index && $row->getData($index)){
            $value = $row->getData($index);
            return '<textarea cols="100" rows="10">' . htmlspecialchars($value) . '</textarea>';
        }
    }
}
