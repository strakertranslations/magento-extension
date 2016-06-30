<?php

namespace Straker\EasyTranslationPlatform\Block\Adminhtml\Setup\StoreLanguage\Form;

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
        Session $session
    ) {

        $this->_storeManager = $storeManager;
        $this->_strakerAPIinterface = $strakerAPIInterface;
        $this->_formFactory = $formFactory;
        $this->_Registry = $registry;
        $this->session = $session;

        parent::__construct($context,$registry,$formFactory);
    }


    protected function _construct()
    {
        parent::_construct();
    }

    protected function _prepareForm()
    {

        $form = $this->_formFactory->create(
            ['data' => ['id' => 'edit_form', 'action' => $this->getData('action'), 'method' => 'post']]
        );

        $form->setHtmlIdPrefix('item_');

        $fieldset = $form->addFieldset(
            'base_fieldset',
            ['legend' => __('Select your Destination Store View'), 'class' => 'fieldset-wide']
        );


        $field = $fieldset->addField(
            'destination',
            'select',
            [
                'name' 	=> 'destination',
                'title'	=> __('destination')
            ]
        );

        $renderer = $this->getLayout()->createBlock(
            'Straker\EasyTranslationPlatform\Block\Adminhtml\Form\Renderer\Sourcefield'
        );

        $field->setRenderer($renderer);

        $this->setForm($form);

        return parent::_prepareForm();
    }

    public function getWebsites() {

        return $this->_storeManager->getWebsites();
    }

}