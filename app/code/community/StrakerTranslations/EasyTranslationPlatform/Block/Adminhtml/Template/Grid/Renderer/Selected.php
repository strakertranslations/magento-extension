<?php
class StrakerTranslations_EasyTranslationPlatform_Block_Adminhtml_Template_Grid_Renderer_Selected
    extends Mage_Adminhtml_Block_Widget_Grid_Column_Renderer_Abstract
{
    public function render(Varien_Object $row){

        if (get_class($row) == get_class(Mage::getModel('strakertranslations_easytranslationplatform/job') )) {

            switch ($row->getTypeName()) {
                case 'Product':
                    $productAttributeCollection =
                      Mage::getModel('strakertranslations_easytranslationplatform/product_attributes')
                        ->getCollection()->addFieldToFilter('job_id', $row->getId());

                    $output =array();

                    foreach ($productAttributeCollection as $productAttribute) {
                        $output[] = $this->_getAttributeLabel($productAttribute->getAttributeId());
                    }
                    $html = implode(', ', $output);
                    break;
                case 'Category':
                    $categoryAttributeCollection =
                    Mage::getModel('strakertranslations_easytranslationplatform/category_attributes')
                      ->getCollection()->addFieldToFilter('job_id', $row->getId());

                    $output =array();

                    foreach ($categoryAttributeCollection as $categoryAttribute) {
                    $output[] = $this->_getAttributeLabel($categoryAttribute->getAttributeId());
                    }
                    $html = implode(', ', $output);
                    break;
                case 'Attribute':
                    $AttributeCollection =
                      Mage::getModel('strakertranslations_easytranslationplatform/job_attribute')
                        ->getCollection()->addFieldToFilter('job_id', $row->getId());

                    $output = count($AttributeCollection);
                    $html = $output > 1 ? "$output Attributes" : '1 Attribute';
                    break;
                case 'CMS Block':
                    $cmsBlockAttributeCollection =
                        Mage::getModel('strakertranslations_easytranslationplatform/cms_block_attributes')
                            ->getCollection()->addFieldToFilter('job_id', $row->getId());

                    $output =array();

                    foreach ($cmsBlockAttributeCollection as $cmsAttribute) {
                        $output[] = $this->_formatColumnName($cmsAttribute->getData('column_name'));
                    }
                    $html = implode(', ', $output);
                    break;
                case 'CMS Page':
                    $cmsPageAttributeCollection =
                        Mage::getModel('strakertranslations_easytranslationplatform/cms_page_attributes')
                            ->getCollection()->addFieldToFilter('job_id', $row->getId());

                    $output =array();

                    foreach ($cmsPageAttributeCollection as $cmsAttribute) {
                        $output[] = $this->_formatColumnName($cmsAttribute->getData('column_name'));
                    }
                    $html = implode(', ', $output);
                    break;
                default:
                    $html = '';
            }

            return $html;
        }

    }

   private function _getAttributeLabel($attributeId) {
     return  Mage::getModel('eav/entity_attribute')->load($attributeId)->getFrontendLabel();
   }

   private function _formatColumnName( $columnName ){
        $return = '';
        switch(strtolower($columnName)){
            case 'meta_keywords':
                $return = 'Meta Keywords';
                break;
            case 'meta_description':
                $return = 'Meta Description';
                break;
            case 'content_heading':
                $return = 'Content Heading';
                break;
            default:
                $return = ucfirst($columnName);
        }
        return $return;
   }
}
