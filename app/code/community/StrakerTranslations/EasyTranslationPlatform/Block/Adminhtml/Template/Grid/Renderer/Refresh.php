<?php
class StrakerTranslations_EasyTranslationPlatform_Block_Adminhtml_Template_Grid_Renderer_Refresh
    extends Mage_Adminhtml_Block_Widget_Grid_Column_Renderer_Abstract
{
    public function render(Varien_Object $row)
    {
        if (get_class($row) == get_class(Mage::getModel('strakertranslations_easytranslationplatform/job'))) {
            $out = '<button class="straker-refresh-button" id="button-update-'.$row->getId().'" onclick="event.stopPropagation(); setLocation(\'' . Mage::helper("adminhtml")->getUrl("adminhtml/straker_job/updateJob", array('job_id' => $row->getId())) . '\')" title="Refresh" type="button">Refresh</button>';
            return $out;
        }
    }
}
