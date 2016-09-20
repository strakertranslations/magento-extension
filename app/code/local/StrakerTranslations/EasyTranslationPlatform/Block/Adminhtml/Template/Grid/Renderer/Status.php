<?php
class StrakerTranslations_EasyTranslationPlatform_Block_Adminhtml_Template_Grid_Renderer_Status
    extends Mage_Adminhtml_Block_Widget_Grid_Column_Renderer_Abstract
{
    public function render(Varien_Object $row){
        if (get_class($row) == get_class(Mage::getModel('strakertranslations_easytranslationplatform/job') )) {
            //if status is QUEUED
            if ($row->getStatusId() == 2) {
                $quotes = json_decode($row->getQuote(), true);
                $html = $this->__('Waiting for Quote');
                if (!empty($quotes) && $row->getStatusId() == 2) {
                    foreach ($quotes as $quote) {
                        if ($quote['quote']) {
                            $html = '<button onclick="viewStrakerQuote('.$row->getId().',\'' . $row->getJobKey() . '\')" style="margin: 5px;" title="View Quote" type="button" style="">'.$this->__('View Quote').'</button>';
                            break;
                        }
                    }
                }
            }
            //else if status is IN_PROGRESS
            elseif ($row->getStatusId() == 3) {
                $html = $this->__('In Progress');
            }
            //else if status is COMPLETED
            elseif ($row->getStatusId() == 4) {
                $html = $this->__('Ready to Publish');
            }
            return $html;
        }

    }
}
