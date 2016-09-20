<?php
class StrakerTranslations_EasyTranslationPlatform_Block_Adminhtml_Template_Grid_Renderer_Selected
    extends Mage_Adminhtml_Block_Widget_Grid_Column_Renderer_Abstract
{
    public function render(Varien_Object $row){

        if (get_class($row) == get_class(Mage::getModel('strakertranslations_easytranslationplatform/job') )) {

            switch ($row->getTypeName()) {
                case 'Product':

                    $productAttrubuteCollection =
                      Mage::getModel('strakertranslations_easytranslationplatform/product_attributes')
                        ->getCollection()->addFieldToFilter('job_id', $row->getId());

                    $output =array();

                    foreach ($productAttrubuteCollection as $productAttrubute) {
                        $output[] = $this->_getAttributeLabel($productAttrubute->getAttributeId());
                    }
                    $html = implode(', ', $output);

                    break;

                case 'Category':

                  $categoryAttrubuteCollection =
                    Mage::getModel('strakertranslations_easytranslationplatform/category_attributes')
                      ->getCollection()->addFieldToFilter('job_id', $row->getId());

                  $output =array();

                  foreach ($categoryAttrubuteCollection as $categoryAttrubute) {
                    $output[] = $this->_getAttributeLabel($categoryAttrubute->getAttributeId());
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

                default:
                    $html = '';

            }

            return $html;
        }

    }

   private function _getAttributeLabel($attributeId) {
     return  Mage::getModel('eav/entity_attribute')->load($attributeId)->getFrontendLabel();
   }
}
