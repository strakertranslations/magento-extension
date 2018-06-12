<?php
class StrakerTranslations_EasyTranslationPlatform_Block_Adminhtml_Template_Grid_Renderer_Path
    extends Mage_Adminhtml_Block_Widget_Grid_Column_Renderer_Abstract
{
    public function render(Varien_Object $row)
    {
        $sourceStoreId = $this->getRequest()->getParam('source_store_id', Mage_Catalog_Model_Abstract::DEFAULT_STORE_ID);
        $cats = explode('/', $row->getPath());
        $catNames = array();
        foreach($cats as $catId){
            if ($catId == '1'){
                continue;
            }
            $catNames[] = Mage::getModel('catalog/category')->setStoreId($sourceStoreId)->load($catId)->getName();
        }

        return implode(' / ', $catNames);
    }
}
