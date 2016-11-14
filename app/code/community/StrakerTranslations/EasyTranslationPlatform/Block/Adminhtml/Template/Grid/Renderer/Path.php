<?php
class StrakerTranslations_EasyTranslationPlatform_Block_Adminhtml_Template_Grid_Renderer_Path
    extends Mage_Adminhtml_Block_Widget_Grid_Column_Renderer_Abstract
{
    public function render(Varien_Object $row)
    {
         $cats = explode('/',$row->getPath());
        $catNames = array();
        foreach($cats as $catId){
            if ($catId == '1'){
                continue;
            }
            $catNames[] = Mage::getModel('catalog/category')->load($catId)->getName();
        }
            return implode(' / ', $catNames);
    }
}
