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


        $fieldset = $form->addFieldset(
            'fieldset',
            ['legend' => __(''), 'class' => '']
        );

        $fieldset->addField('default_attributes', 'multiselect', array(
            'label' => __('Default Attributes'),
            'name' => 'default[]',
            'required' => true,
            'values' =>  $this->getDefaultAttributes()
        ));

        $fieldset->addField('custom_attributes', 'multiselect', array(
            'label' => __('Custom Attributes'),
            'name' => 'custom[]',
            'values' =>  $this->getCustomAttributes(),
        ));


        $form->setUseContainer(true);

        //$form->setValues('default_attributes',array(70));

        //$form->setValues($this->session->getData('form_data'));

        $this->setForm($form);

        return parent::_prepareForm();
    }


    public function getDefaultAttributes()
    {

        $values = [];

        $attributes = $this->productHelper->getDefaultAttributes();

        foreach ($attributes as $attribute){

            $values[] = ['value' => $attribute->getAttributeId(),'label' => $attribute->getData('frontend_label')];

        }

        usort($values,function($a,$b){

            return strcmp($a['label'], $b['label']);

        });


        return $values;
    }

    public function getCustomAttributes()
    {

        $values = [];

        $attributes = $this->productHelper->getCustomAttributes();

        foreach ($attributes as $attribute){

            $values[] = ['value' => $attribute->getAttributeId(),'label' => $attribute->getFrontendLabel()];
        }

        usort($values,function($a,$b){

            return strcmp($a['label'], $b['label']);

        });

        return $values;
    }

}
