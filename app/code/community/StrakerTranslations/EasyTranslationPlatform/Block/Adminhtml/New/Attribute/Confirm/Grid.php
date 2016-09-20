<?php
class StrakerTranslations_EasyTranslationPlatform_Block_Adminhtml_New_Attribute_Confirm_Grid extends Mage_Adminhtml_Block_Widget_Grid
{

    public function __construct()
    {
        parent::__construct();
        $this->setId('strakerAttribute');
        $this->setDefaultSort('attribute_code');
        $this->setDefaultDir('ASC');
        $this->setSaveParametersInSession(true);
        $this->setUseAjax(false);
    }

    protected function _prepareLayout()
    {
        return $this;
    }

    protected function _prepareColumns()
    {
        parent::_prepareColumns();


        $this->addColumn('attribute_code', array(
            'header'=>Mage::helper('strakertranslations_easytranslationplatform')->__('Attribute Code'),
            'sortable'=>true,
            'filter'    => false,
            'index'=>'attribute_code'
        ));

        $this->addColumn('frontend_label', array(
            'header'=>Mage::helper('strakertranslations_easytranslationplatform')->__('Attribute Label'),
            'sortable'=>true,
            'filter'    => false,
            'index'=>'frontend_label'
        ));

        $this->addColumn('translate_label', array(
            'header'=>Mage::helper('strakertranslations_easytranslationplatform')->__('Translate Attribute Label'),
            'renderer' => 'StrakerTranslations_EasyTranslationPlatform_Block_Adminhtml_Template_Grid_Renderer_ConfirmTranslateLabel',
            'align' => 'center',
            'index' => false,
            'sortable'=> false,
            'filter'    => false,
        ));

        $this->addColumn('translate_options', array(
            'header'=>Mage::helper('strakertranslations_easytranslationplatform')->__('Translate Attribute Options'),
            'renderer' => 'StrakerTranslations_EasyTranslationPlatform_Block_Adminhtml_Template_Grid_Renderer_ConfirmTranslateOptions',
            'align' => 'center',
            'index' => false,
            'sortable'=> false,
            'filter'    => false,
        ));

        $this->addColumn('is_visible', array(
            'header'=>Mage::helper('catalog')->__('Visible'),
            'sortable'=>true,
            'index'=>'is_visible_on_front',
            'type' => 'options',
            'filter'    => false,
            'options' => array(
                '1' => Mage::helper('catalog')->__('Yes'),
                '0' => Mage::helper('catalog')->__('No'),
            ),
            'align' => 'center',
        ));

        return $this;
    }

    protected function _prepareCollection()
    {
        if ($this->getOption()) {
            $attributeIds = array_unique(
                array_merge(
                    $this->getAttribute(),
                    explode(',', $this->getOption())
                )
            );
        }
        else {
            $attributeIds = $this->getAttribute();
        }
        $collection = Mage::getResourceModel('catalog/product_attribute_collection')
            ->addFieldToFilter('main_table.attribute_id', $attributeIds)
            ->addVisibleFilter();
        $this->setCollection($collection);

        return parent::_prepareCollection();
    }

    public function getGridUrl()
    {
        return $this->getUrl('*/*/addtoconfirm', array('_current'=>true));
    }

    public function getRowUrl($row)
    {
        return '';
    }

    public function getAttrArray(){
        return explode(',', $this->getAttr());
    }
}
