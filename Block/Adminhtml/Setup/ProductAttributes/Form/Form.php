<?php

namespace Straker\EasyTranslationPlatform\Block\Adminhtml\Setup\ProductAttributes\Form;

use Magento\Backend\Block\Template\Context;
use Magento\Framework\Registry;
use Magento\Framework\Data\FormFactory;
use Magento\Store\Model\System\Store;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Backend\Model\Session;

use Straker\EasyTranslationPlatform\Helper\ProductHelper;

class Form extends \Magento\Backend\Block\Widget\Form\Generic
{

    public function __construct(
        Context $context,
        Registry $registry,
        FormFactory $formFactory,
        StoreManagerInterface $storeManager,
        Session $session,
        ProductHelper $productHelper,
        array $data = []
    ) {

        $this->_storeManager = $storeManager;
        $this->_formFactory = $formFactory;
        $this->_Registry = $registry;
        $this->session = $session;
        $this->productHelper = $productHelper;

        parent::__construct($context,$registry,$formFactory,$data);
    }


    protected function _construct()
    {
        parent::_construct();
    }

    protected function _prepareForm()
    {

        /** @var \Magento\Framework\Data\Form $form */
        $form = $this->_formFactory->create(
            ['data' => ['id' => 'edit_form', 'action' => $this->getData('action'), 'method' => 'post']]
        );

        $form->setHtmlIdPrefix('attribute_');



        $fieldset = $form->addFieldset(
            'fieldset',
            ['legend' => __(''), 'class' => '']
        );


        $fieldset->addField('Default Attributes', 'checkboxes', array(
            'label' => __('Default Attributes'),
            'name' => 'default[]',
            'required' => true,
            'checked' => $this->getDefaultAttributes()['default_values'],
            'values' =>  $this->getDefaultAttributes()['options'],
            'onclick' => "",
            'onchange' => "",
            'disabled' => false,
            'value'  => '1',
            'tabindex' => 1
        ));

        $fieldset->addField('Custom Attributes', 'checkboxes', array(
            'label' => __('Custom Attributes'),
            'name' => 'custom[]',
            'required' => true,
            'checked' => true,
            'values' =>  $this->getCustomAttributes(),
            'onclick' => "",
            'onchange' => "",
            'disabled' => false,
            'value'  => '1',
            'tabindex' => 1
        ));



//        foreach ($this->productHelper->getDefaultAttributes() as $attribute){
//
//            $fieldset_1->addField(
//                'default_attribute_'.$attribute->getAttributeId(),
//                'checkbox',
//                [
//                    'name'=>'default_'.$attribute->getAttributeId(),
//                    'title'=> 'default_attributes',
//                    'checked'=>true,
//                    'value'=>'1',
//                    'onclick'   => 'this.value = this.checked ?'.$attribute->getAttributeId().' : 0;',
//                    'after_element_html'=>'&nbsp;'.$attribute->getFrontendLabel()
//                ]
//            );
//
//        }
//
//        foreach ($this->productHelper->getCustomAttributes() as $attribute){
//
//            $fieldset_2->addField(
//                'custom_attributes_'.$attribute->getAttributeId(),
//                'checkbox',
//                [
//                    'name'=>'custom_'.$attribute->getAttributeId(),
//                    'title'=> 'default_attributes',
//                    'onclick'   => 'this.value = this.checked ? '.$attribute->getAttributeId().' : 0;',
//                    'after_element_html'=>'&nbsp;'.$attribute->getFrontendLabel()
//                ]
//            );
//
//        }


        $form->setUseContainer(true);

        $form->setValues($this->session->getData('form_data'));

        $this->setForm($form);

        return parent::_prepareForm();
    }


    public function getDefaultAttributes()
    {
        $options = [];

        $values = [];

        $attributes = $this->productHelper->getDefaultAttributes();

        foreach ($attributes as $attribute){

                $options[] = array('value'=>$attribute->getAttributeId(),'label'=> $attribute->getFrontendLabel(),'name'=>'test');
                $values[] = $attribute->getAttributeId();
        }

        $options['options'] = $options;

        $options['default_values'] = $values;

        return $options;
    }

    public function getCustomAttributes()
    {

        $options = [];

        $attributes = $this->productHelper->getCustomAttributes();

        foreach ($attributes as $attribute){

            $options[] = array('value'=>$attribute->getAttributeId(),'label'=> $attribute->getFrontendLabel());
        }

        return $options;
    }
}
