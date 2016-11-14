<?php
class StrakerTranslations_EasyTranslationPlatform_Block_Adminhtml_Template_Grid_Renderer_Status
    extends Mage_Adminhtml_Block_Widget_Grid_Column_Renderer_Abstract
{
    public function render(Varien_Object $row){
        if (get_class($row) == get_class(Mage::getModel('strakertranslations_easytranslationplatform/job') )) {
            $html = '';

            //if status is QUEUED
            if ($row->getStatusName() == 'QUEUED') {
                $quote = $row->getQuote();
                $html = $this->__('Waiting for Quote');
                if ('READY' === $quote && $row->getStatusId() == 2) {
                        $html = '<button onclick="viewStrakerQuote('.$row->getId().',\'' . $row->getJobKey() . '\')" style="margin: 5px;" title="View Quote" type="button" style="">'.$this->__('View Quote').'</button>';
                    }
                }
            //else if status is IN_PROGRESS
            elseif ($row->getStatusName() == 'IN_PROGRESS') {
                $html = $this->__('In Progress');
            }
            //else if status is COMPLETED
            elseif ($row->getStatusName() == 'COMPLETED') {
                $html = $this->__('Ready to Publish');
            }
            //else if status is PUBLISHED
            elseif ($row->getStatusName() == 'PUBLISHED') {
                $html = $this->__('Published');
            }
            return $html;
        }

    }
}
