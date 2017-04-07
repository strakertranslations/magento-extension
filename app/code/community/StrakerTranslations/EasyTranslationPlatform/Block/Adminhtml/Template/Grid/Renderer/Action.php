<?php
class StrakerTranslations_EasyTranslationPlatform_Block_Adminhtml_Template_Grid_Renderer_Action
    extends Mage_Adminhtml_Block_Widget_Grid_Column_Renderer_Abstract
{
    public function render(Varien_Object $row)
    {
        $out = '';
        if (get_class($row) == get_class(Mage::getModel('strakertranslations_easytranslationplatform/job'))) {

            $buttonText = $row->getStatusId() == 4 ? $this->__('Confirm') : $this->__('View');

            $out = '<button id="button-update-'.$row->getId().'" style="margin: 5px; display: block;" onclick="event.stopPropagation(); setLocation(\'' . Mage::helper("adminhtml")->getUrl("adminhtml/straker_".str_replace(' ', '_', strtolower($row->getTypeName())).'/', array('job_id' => $row->getId())) . '\')" title="View" type="button" style="">'.$buttonText.'</button>';
        }
        return $out;
    }
}
