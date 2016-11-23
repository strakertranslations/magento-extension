<?php

namespace Straker\EasyTranslationPlatform\Block\Adminhtml\Setup\TestingStoreView\Form;

use Straker\EasyTranslationPlatform\Api\Data\StrakerAPIInterface;

use Magento\Backend\Block\Template\Context;
use Magento\Framework\Registry;
use Magento\Framework\Data\FormFactory;
use Magento\Store\Model\System\Store;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Backend\Model\Session;

class Form extends \Magento\Backend\Block\Widget\Form\Generic
{

    protected $_strakerAPIInterface;
    protected $_Registry;
    protected $session;

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
        $this->_strakerAPIInterface = $strakerAPIInterface;
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
