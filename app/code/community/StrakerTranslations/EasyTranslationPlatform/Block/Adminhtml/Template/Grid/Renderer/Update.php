<?php
class StrakerTranslations_EasyTranslationPlatform_Block_Adminhtml_Template_Grid_Renderer_Update
    extends Mage_Adminhtml_Block_Widget_Grid_Column_Renderer_Abstract
{
    public function render(Varien_Object $row)
    {
        if (get_class($row) == get_class(Mage::getModel('strakertranslations_easytranslationplatform/job'))) {
            $out = '<button id="button-update-'.$row->getId().'" style="margin: 5px; display: block;" onclick="event.stopPropagation(); setLocation(\'' . Mage::helper("adminhtml")->getUrl("straker/adminhtml_job/updateJob", array('job_id' => $row->getId())) . '\')" title="Update" type="button" style=""><span><span><span>Update</span></span></span></button>';
            return $out;
        }
    }
}
