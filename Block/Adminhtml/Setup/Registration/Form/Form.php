<?php

namespace Straker\EasyTranslationPlatform\Block\Adminhtml\Setup\Registration\Form;

use Straker\EasyTranslationPlatform\Api\Data\StrakerAPIInterface;

use Magento\Backend\Block\Template\Context;
use Magento\Framework\Registry;
use Magento\Framework\Data\FormFactory;
use Magento\Store\Model\System\Store;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Backend\Model\Session;

class Form extends \Magento\Backend\Block\Widget\Form\Generic
{

    public function __construct(
        Context $context,
        Registry $registry,
        FormFactory $formFactory,
        StoreManagerInterface $storeManager,
        StrakerAPIInterface $strakerAPIInterface,
        Session $session,
        array $data = []
    ) {

        $this->_storeManager = $storeManager;
        $this->_strakerAPIinterface = $strakerAPIInterface;
        $this->_formFactory = $formFactory;
        $this->_Registry = $registry;
        $this->session = $session;

        parent::__construct($context, $registry, $formFactory, $data);
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

        $form->setHtmlIdPrefix('item_');

        $fieldset = $form->addFieldset(
            'base_fieldset',
            ['legend' => __(' '), 'class' => 'fieldset-wide']
        );

        $fieldset->addField(
            'first_name',
            'text',
            [
                'name' => 'first_name',
                'label' => __('First Name'),
                'title' => __('first_name'),
                'required' => true
            ]
        );

        $fieldset->addField(
            'last_name',
            'text',
            [
                'name' => 'last_name',
                'label' => __('Last Name'),
                'title' => __('last_name'),
                'required' => true
            ]
        );

        $fieldset->addField(
            'email',
            'text',
            [
                'name' => 'email',
                'label' => __('Email'),
                'title' => __('email'),
                'required' => true,
                'class'=>'validate-email'
            ]
        );

        $fieldset->addField(
            'country',
            'select',
            [
                'label' => __('Country'),
                'title' => __('country'),
                'name' => 'country',
                'required' => true,
                'options' => $this->_getOptions()
            ]
        );

        $fieldset->addField(
            'company_name',
            'text',
            [
                'name' => 'company_name',
                'label' => __('Company Name'),
                'title' => __('company_name')
            ]
        );

        $fieldset->addField(
            'phone_number',
            'text',
            [
                'name' => 'phone_number',
                'label' => __('Phone Number'),
                'title' => __('phone_Number')
            ]
        );

        $fieldset->addField(
            'url',
            'text',
            [
                'name' => 'url',
                'label' => __('Website Url'),
                'title' => __('url'),
                'class'=>'validate-url'
            ]
        );

        $fieldset->addField(
            'terms',
            'checkbox',
            [
                'label' => __(' '),
                'name' => 'terms',
                'after_element_html' => '<span>&nbsp;&nbsp;I have read and agreed to the</span><a href="https://www.strakertranslations.com/terms-conditions/" target="_blank"> terms and conditions</a>',
                'class'=>'checkbox required'
            ]
        );

        $form->setUseContainer(true);

        $form->setValues($this->session->getData('form_data'));

        $this->setForm($form);

        return parent::_prepareForm();
    }

    protected function _getOptions()
    {
        $aCountries = [];

        $aCountries[null] = 'Select a country';

        foreach ($this->_strakerAPIinterface->getCountries() as $key => $value) {
            $aCountries[$value->code] = $value->name;
        }

        return $aCountries;
    }
}
