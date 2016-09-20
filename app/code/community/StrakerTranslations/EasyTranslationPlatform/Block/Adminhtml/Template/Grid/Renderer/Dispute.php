<?php
class StrakerTranslations_EasyTranslationPlatform_Block_Adminhtml_Template_Grid_Renderer_Dispute
    extends Mage_Adminhtml_Block_Widget_Grid_Column_Renderer_Abstract
{
    public function render(Varien_Object $row){
            $quotes = json_decode($row->getQuote(), true);
            $html = '<button style="margin: 5px; display: block;" onclick="disputeForm.show(\''.$row->getJobId().'\',\''.$row->getProductId().'\')" title="Update" type="button" style=""><span><span><span>Dispute</span></span></span></button>';
            return $html;
    }

}
