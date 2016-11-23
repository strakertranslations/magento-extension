<?php
class StrakerTranslations_EasyTranslationPlatform_Block_Adminhtml_Cms_Page_Grid extends Mage_Adminhtml_Block_Cms_Page_Grid {
    protected function _prepareCollection()
    {
        /* @var $collection Mage_Cms_Model_Resource_Page_Collection */
        $collection = Mage::getModel('cms/page')->getCollection();
//        $prefix = Mage::getConfig()->getTablePrefix()->__toString();
        $collection->getSelect()->joinLeft(
            array('straker' => $collection->getTable('strakertranslations_easytranslationplatform/job_cmspage')),
            'straker.new_entity_id = main_table.page_id',
            array('straker_translated' => 'straker.version')
        );
        $collection->setFirstStoreFlag(true);
        $this->setCollection($collection);

        return Mage_Adminhtml_Block_Widget_Grid::_prepareCollection();
    }

    protected function _prepareColumns()
    {
        if (!Mage::app()->isSingleStoreMode()) {
            $this->addColumnAfter('straker_translated', array(
                'header'    => Mage::helper('strakertranslations_easytranslationplatform')->__('Created by Straker'),
                'type'      => 'options',
                'options'   => array('0' => 'No', '1' => 'Yes'),
                'renderer'  => 'StrakerTranslations_EasyTranslationPlatform_Block_Adminhtml_Template_Grid_Renderer_StrakerTranslated',
                'index'     => 'straker_translated',
                'filter_index' => 'straker.version',
                'filter_condition_callback' => array($this, '_filterStrakerVersion')
            ), 'page_actions');
        }
        return parent::_prepareColumns();
    }

    protected function _filterStrakerVersion($collection, $column)
    {
        $value = $column->getFilter()->getValue();
        if ($value == 0) {
            $this->getCollection()->getSelect()->where(
                "straker.version IS NULL");
        }
        else {
            $this->getCollection()->getSelect()->where(
                "straker.version = ".$value);
        }

        return $this;
    }
}