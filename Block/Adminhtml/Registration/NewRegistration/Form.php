<?php

namespace Straker\EasyTranslationPlatform\Block\Adminhtml\Registration\NewRegistration;

use Straker\EasyTranslationPlatform\Api\Data\StrakerAPIInterface;

use Magento\Backend\Block\Template\Context;
use Magento\Framework\Registry;
use Magento\Framework\Data\FormFactory;
use Magento\Cms\Model\Wysiwyg\Config;
use Magento\Store\Model\System\Store;
use Magento\Store\Model\StoreManagerInterface;

class Form extends \Magento\Backend\Block\Widget\Form\Generic
{
    /**
     * @var \Magento\Cms\Model\Wysiwyg\Config
     */
    protected $_wysiwygConfig;

    /**
     * @var \Magento\Store\Model\System\Store
     */
    protected $_systemStore;

    /**
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Framework\Data\FormFactory $formFactory
     * @param \Magento\Cms\Model\Wysiwyg\Config $wysiwygConfig
     * @param \Magento\Store\Model\System\Store $systemStore
     * @param array $data
     */
    public function __construct(
        Context $context,
        Registry $registry,
        FormFactory $formFactory,
        Config $wysiwygConfig,
        Store $systemStore,
        StoreManagerInterface $storeManager,
        StrakerAPIInterface $strakerAPIInterface
    ) {
        $this->_wysiwygConfig = $wysiwygConfig;
        $this->_systemStore = $systemStore;
        $this->_storeManager = $storeManager;
        $this->_strakerAPIinterface = $strakerAPIInterface;
        $this->_formFactory = $formFactory;
        $this->_Registry = $registry;

        parent::__construct($context,$registry,$formFactory);
    }

    /**
     * Init form
     *
     * @return void
     */
    protected function _construct()
    {
        parent::_construct();
    }

    /**
     * Prepare form
     *
     * @return $this
     */
    protected function _prepareForm()
    {
        $model = $this->_coreRegistry->registry('test_item');

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
                'title' => __('company_name'),
                'required' => true
            ]
        );

        $fieldset->addField(
            'phone_number',
            'text',
            [
                'name' => 'phone_number',
                'label' => __('Phone Number'),
                'title' => __('phone_Number'),
                'required' => true
            ]
        );

        $fieldset->addField(
            'url',
            'text',
            [
                'name' => 'url',
                'label' => __('Website Url'),
                'title' => __('url'),
                'required' => true,
                'class'=>'validate-url'
            ]
        );

        $fieldset->addField(
            'terms',
            'checkboxes',
            [
                'label' => __(' '),
                'name' => 'terms',
                'values' => [
                    ['value' => '1','label' => 'I have read and agreed to the <a href="https://www.strakertranslations.com/terms-conditions/"> terms and conditions</a>']
                ],
                'required' => true
            ]
        );

        $form->setUseContainer(true);
        $this->setForm($form);

        return parent::_prepareForm();
    }

    protected function _getOptions(
    ){
        $aCountries = [];

        $aCountries[NULL] = 'Select-A-Country';

        foreach($this->_strakerAPIinterface->getCountries() as $key => $value)
        {
            $aCountries[$value->code] = $value->name;
        }

        return $aCountries;
    }

}