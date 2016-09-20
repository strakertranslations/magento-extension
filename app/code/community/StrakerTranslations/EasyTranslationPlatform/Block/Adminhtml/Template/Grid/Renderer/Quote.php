<?php
class StrakerTranslations_EasyTranslationPlatform_Block_Adminhtml_Template_Grid_Renderer_Quote
    extends Mage_Adminhtml_Block_Widget_Grid_Column_Renderer_Abstract
{
    public function render(Varien_Object $row){
        $html = '';
        if (get_class($row) == get_class(Mage::getModel('strakertranslations_easytranslationplatform/job') )) {
            $quotes = json_decode($row->getQuote(), true);
            if(is_array($quotes)) {
                foreach ($quotes as $quote) {
                    $html .= "<div class='quote-workflow'>";
                    foreach ($quote as $k => $v) {
                        $html .= ($k == 'workflow') ? "<h4>{$v}</h4>" : "<label>{$k}: </label><span>{$v}</span><br />";
                    }
                    $html .= "</div>";
                }
            }
        }
        return $html;
    }

}
