<?php

class StrakerTranslations_EasyTranslationPlatform_Block_Adminhtml_Job_Grid extends Mage_Adminhtml_Block_Widget_Grid {

    public function __construct() {
        parent::__construct();
        $this->setId('strakerJobGrid');
        $this->setDefaultSort('id');
        $this->setDefaultDir('DESC');
        $this->setSaveParametersInSession(true);
    }

    protected function _prepareCollection() {
        /** @var $collection StrakerTranslations_EasyTranslationPlatform_Model_Resource_Job_Collection*/
        $collection = Mage::getModel('strakertranslations_easytranslationplatform/job')->getCollection();

        foreach($collection as $jobModel){
            if($jobModel->getStatusId() == 4){
                $jobModel->load($jobModel->getId())->isPublished();
            }
        }

        $collection = Mage::getModel('strakertranslations_easytranslationplatform/job')->getCollection();

        /** @var $helper StrakerTranslations_EasyTranslationPlatform_Helper_Data */
        $helper = Mage::helper('strakertranslations_easytranslationplatform');
        if($helper->isSandboxMode()){
            $collection->addFieldToFilter('is_test_job', ['eq' => true]);
        }else{
            $collection->addFieldToFilter('is_test_job', ['eq' => false]);
        }

        $collection->getSelect()->joinLeft(
            Mage::getSingleton('core/resource')->getTableName('strakertranslations_easytranslationplatform/job_type'),
            Mage::getSingleton('core/resource')->getTableName('strakertranslations_easytranslationplatform/job_type').'.type_id = main_table.type_id',
            'type_name'
        );
        $collection->getSelect()->joinLeft(
            Mage::getSingleton('core/resource')->getTableName('strakertranslations_easytranslationplatform/job_status'),
            Mage::getSingleton('core/resource')->getTableName('strakertranslations_easytranslationplatform/job_status').'.status_id = main_table.status_id',
            'status_name'
        );
        $collection->getSelect()->joinLeft(
            Mage::getSingleton('core/resource')->getTableName('core/store'),
            Mage::getSingleton('core/resource')->getTableName('core/store').'.store_id = main_table.store_id',
            'name'
        );
        $this->setCollection($collection);
        return parent::_prepareCollection();
    }

    protected function _prepareColumns() {
        $this->addColumn('id', array(
            'header' => Mage::helper('strakertranslations_easytranslationplatform')->__('ID'),
            'align' => 'right',
            'width' => '50px',
            'index' => 'id',
        ));

        $this->addColumn('tj_number', array(
            'header' => Mage::helper('strakertranslations_easytranslationplatform')->__('Straker Ref.'),
            'align' => 'left',
            'index' => 'tj_number',
        ));

        $this->addColumn('created_at', array(
            'header' => Mage::helper('strakertranslations_easytranslationplatform')->__('Created At'),
            'align' => 'left',
            'type' => 'datetime',
            'index' => 'created_at',
        ));

        $this->addColumn('updated_at', array(
            'header' => Mage::helper('strakertranslations_easytranslationplatform')->__('Updated At'),
            'align' => 'left',
            'type' => 'datetime',
            'index' => 'updated_at',
        ));

        $this->addColumn('type_name', array(
            'header' => Mage::helper('strakertranslations_easytranslationplatform')->__('Content Type'),
            'align' => 'left',
            'index' => 'type_name',
        ));

        $this->addColumn('selected', array(
          'header'    => Mage::helper('strakertranslations_easytranslationplatform')->__('Content Selected'),
          'renderer' => 'StrakerTranslations_EasyTranslationPlatform_Block_Adminhtml_Template_Grid_Renderer_Selected',
          'align'    => 'center',
          'filter'    => false,
        ));

        $this->addColumn('name', array(
            'header' => Mage::helper('strakertranslations_easytranslationplatform')->__('Store View'),
            'align' => 'left',
            'index' => 'name',
            'filter_index' => Mage::getSingleton('core/resource')->getTableName('core/store').'.name',
        ));

        $this->addColumn('sl', array(
            'header' => Mage::helper('strakertranslations_easytranslationplatform')->__('Source Language'),
            'align' => 'left',
            'index' => 'sl',
        ));

        $this->addColumn('tl', array(
            'header' => Mage::helper('strakertranslations_easytranslationplatform')->__('Target Language'),
            'align' => 'left',
            'index' => 'tl',
        ));

        $this->addColumn('refresh', array(
            'header'    => Mage::helper('strakertranslations_easytranslationplatform')->__('Refresh'),
            'renderer' => 'StrakerTranslations_EasyTranslationPlatform_Block_Adminhtml_Template_Grid_Renderer_Refresh',
            'align'    => 'center',
            'filter'    => false,
        ));

        $this->addColumn('status', array(
            'header'    => Mage::helper('strakertranslations_easytranslationplatform')->__('Status'),
            'renderer' => 'StrakerTranslations_EasyTranslationPlatform_Block_Adminhtml_Template_Grid_Renderer_Status',
            'align'    => 'center',
            'filter'    => false,
        ));

        $this->addColumn('action', array(
            'header'    => Mage::helper('strakertranslations_easytranslationplatform')->__('Action'),
            'renderer' => 'StrakerTranslations_EasyTranslationPlatform_Block_Adminhtml_Template_Grid_Renderer_Action',
            'align'    => 'center',
            'filter'    => false,
        ));

        return parent::_prepareColumns();
    }

    public function getRowUrl($row) {
        return '';
    }

}
