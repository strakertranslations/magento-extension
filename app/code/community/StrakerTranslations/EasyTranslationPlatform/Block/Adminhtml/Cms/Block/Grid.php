<?php
class StrakerTranslations_EasyTranslationPlatform_Block_Adminhtml_Cms_Block_Grid extends Mage_Adminhtml_Block_Cms_Block_Grid {
    protected function _prepareCollection()
    {
        /* @var $collection Mage_Cms_Model_Resource_Block_Collection */
        $collection = Mage::getModel('cms/block')->getCollection();
//        $prefix = Mage::getConfig()->getTablePrefix()->__toString();
        $collection->getSelect()->joinLeft(
            array('straker' => $collection->getTable('strakertranslations_easytranslationplatform/job_cmsblock')),
            'straker.new_entity_id = main_table.block_id',
            array('straker_translated' => 'straker.version')
        );

        $collection->addFilterToMap('title', 'main_table.title');
        $collection->addFilterToMap('identifier', 'main_table.identifier');

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
            ), 'update_time');
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