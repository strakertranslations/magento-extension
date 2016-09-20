<?php
class StrakerTranslations_EasyTranslationPlatform_Block_Adminhtml_Template_Grid_Renderer_CmsVersion
    extends Mage_Adminhtml_Block_Widget_Grid_Column_Renderer_Abstract
{
    public function render(Varien_Object $row)
    {
        if($row->getVersion()){
            if($row->getBlockId()){
                $link = Mage::helper("adminhtml")->getUrl("adminhtml/cms_block/edit",array("block_id"=>$row->getNewEntityId()));
            }
            else{
                $link = Mage::helper("adminhtml")->getUrl("adminhtml/cms_page/edit",array("page_id"=>$row->getNewEntityId()));
            }
            return '<a href="'.$link.'">View Published</a>';
        }
        else{
            return 'Not Published';
        }
    }
}
