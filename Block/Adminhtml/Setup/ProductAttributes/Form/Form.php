<?php

namespace Straker\EasyTranslationPlatform\Block\Adminhtml\Setup\ProductAttributes\Form;

use Magento\Backend\Block\Template\Context;
use Magento\Backend\Block\Widget\Form\Generic;
use Magento\Framework\Registry;
use Magento\Framework\Data\FormFactory;
use Straker\EasyTranslationPlatform\Helper\ProductHelper;
use Straker\EasyTranslationPlatform\Helper\CategoryHelper;

class Form extends Generic
{
    protected $_Registry;
    protected $productHelper;
    protected $categoryHelper;

    public function __construct(
        Context $context,
        Registry $registry,
        FormFactory $formFactory,
        ProductHelper $productHelper,
        CategoryHelper $categoryHelper,
        array $data = []
    ) {

        $this->_formFactory = $formFactory;
        $this->_Registry = $registry;
        $this->productHelper = $productHelper;
        $this->categoryHelper = $categoryHelper;

        parent::__construct($context, $registry, $formFactory, $data);
    }

    protected function _prepareForm()
    {

        /** @var \Magento\Framework\Data\Form $form */
        $form = $this->_formFactory->create(
            ['data' => ['id' => 'edit_form', 'action' => $this->getData('action'), 'method' => 'post']]
        );


        $fieldset = $form->addFieldset(
            'fieldset',
            ['legend' => __('Product Attributes'), 'class' => 'settings-attributes']
        );

        $fieldset2 = $form->addFieldset(
            'fieldset2',
            ['legend' => __('Category Attributes'), 'class' => 'settings-attributes']
        );

        $fieldset->addField('default_attributes', 'multiselect', [
            'label' => __('Default'),
            'name' => 'default[]',
            'required' => true,
            'values' =>  $this->getDefaultAttributes()[0],
            'value'=> $this->getDefaultAttributes()[1],
            'class'=>'straker-attributes'
        ]);

        $fieldset->addField('custom_attributes', 'multiselect', [
            'label' => __('Custom'),
            'name' => 'custom[]',
            'values' =>  $this->getCustomAttributes()[0],
            'value'=> $this->getCustomAttributes()[1],
            'class'=>'straker-attributes'
        ]);

        $fieldset2->addField('category_attributes', 'multiselect', [
            'label' => __('Default'),
            'name' => 'category[]',
            'required' => true,
            'values' =>  $this->getCategoryAttributes()[0],
            'value'=> $this->getCategoryAttributes()[1],
            'class'=>'straker-attributes'
        ]);


        $form->setUseContainer(true);

        //$form->setValues($this->session->getData('form_data'));

        $this->setForm($form);

        return parent::_prepareForm();
    }


    public function getDefaultAttributes()
    {

        $values = [];
        $default = [];
        $array = [];

        $attributes = $this->productHelper->getDefaultAttributes();

        foreach ($attributes as $attribute) {
            $values[] = ['value' => $attribute->getAttributeId(),'label' => $attribute->getData('frontend_label')];
            $default[] = $attribute->getAttributeId();
        }

        usort($values, function ($a, $b) {

            return strcmp($a['label'], $b['label']);
        });

        $array[] = $values;
        $array[] = $default;

        return $array;
    }

    public function getCustomAttributes()
    {

        $values = [];
        $default = [];
        $array = [];

        $attributes = $this->productHelper->getCustomAttributes();

        foreach ($attributes as $attribute) {
            $values[] = ['value' => $attribute->getAttributeId(),'label' => $attribute->getFrontendLabel()];
            $default[] = $attribute->getAttributeId();
        }

        usort($values, function ($a, $b) {

            return strcmp($a['label'], $b['label']);
        });

        $array[] = $values;
        $array[] = $default;

        return $array;
    }

    public function getCategoryAttributes()
    {

        $values = [];
        $default = [];
        $array = [];

        $attributes = $this->categoryHelper->getAttributes();

        foreach ($attributes as $attribute) {
            $values[] = ['value' => $attribute->getAttributeId(),'label' => $attribute->getFrontendLabel()];
            $default[] = $attribute->getAttributeId();
        }

        usort($values, function ($a, $b) {

            return strcmp($a['label'], $b['label']);
        });

        $array[] = $values;
        $array[] = $default;

        return $array;
    }
}
