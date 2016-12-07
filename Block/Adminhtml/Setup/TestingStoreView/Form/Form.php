<?php

namespace Straker\EasyTranslationPlatform\Block\Adminhtml\Setup\TestingStoreView\Form;

use Magento\Backend\Block\Widget\Form\Generic;
use Magento\Backend\Block\Template\Context;
use Magento\Framework\Registry;
use Magento\Framework\Data\FormFactory;

class Form extends Generic
{

    protected $_strakerAPIInterface;
    protected $_Registry;
    protected $session;

    public function __construct(
        Context $context,
        Registry $registry,
        FormFactory $formFactory,
        array $data = []
    ) {
        $this->_formFactory = $formFactory;
        $this->_Registry = $registry;
        parent::__construct($context, $registry, $formFactory, $data);
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
            'store_view_name',
            'text',
            [
                'name' => 'store_view_name',
                'label' => __('Name'),
                'title' => __('Name of Store View'),
                'required' => false
            ]
        );

        $form->setUseContainer(true);

        $form->setValues($this->session->getData('form_data'));

        $this->setForm($form);

        return parent::_prepareForm();
    }
}
