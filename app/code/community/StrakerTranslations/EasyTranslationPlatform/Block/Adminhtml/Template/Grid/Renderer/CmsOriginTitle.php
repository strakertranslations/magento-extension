<?php
class StrakerTranslations_EasyTranslationPlatform_Block_Adminhtml_Template_Grid_Renderer_CmsOriginTitle
    extends Mage_Adminhtml_Block_Widget_Grid_Column_Renderer_Abstract
{
    public function render(Varien_Object $row)
    {
            $origin = $row->getOrigin() ? json_decode($row->getOrigin(), true): array();
            return $origin['title'] ? $origin['title']:'';
    }
}
