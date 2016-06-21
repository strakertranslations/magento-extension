<?php

namespace Straker\EasyTranslationPlatform\Block\Adminhtml\Store\NewStore;

use Staker\EasyTranslationPlatform\Api\Data\StrakerAPIInterface;
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
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\Data\FormFactory $formFactory,
        \Magento\Cms\Model\Wysiwyg\Config $wysiwygConfig,
        \Magento\Store\Model\System\Store $systemStore,
        \Straker\EasyTranslationPlatform\Api\Data\StrakerAPIInterface $strakerAPIInterfaceInterface,
        \Magento\Store\Model\StoreManagerInterface $storeManager
    ) {
        $this->_wysiwygConfig = $wysiwygConfig;
        $this->_systemStore = $systemStore;
        $this->_strakerAPI = $strakerAPIInterfaceInterface;
        $this->_storeManager = $storeManager;
        parent::__construct($context, $registry, $formFactory);
    }

    /**
     * Init form
     *
     * @return void
     */
    protected function _construct()
    {
        parent::_construct();
        $this->setId('test_form');
        $this->setTitle(__('Item Information'));
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
            ['legend' => __('Select Your Language'), 'class' => 'fieldset-wide']
        );

        $fieldset->addField(
            'name',
            'hidden',
            [
                'name' => 'name',
                'label' => __('Name'),
                'title' => __('Name'),
                'required' => true
            ]
        );

        $fieldset->addField(
            'general_locale_code',
            'select',
            [
                'label' => __('Language'),
                'title' => __('Locale'),
                'name' => 'general_locale_code',
                'required' => true,
                'options' => $this->_getOptions()
            ]
        );

//       $form->setValues($model->getData());
        $form->setUseContainer(true);
        $this->setForm($form);

        return parent::_prepareForm();
    }

    protected function _getOptions(
    ){
        $aCountries = [];

        foreach($this->_strakerAPI->getCountries() as $key => $value)
        {
            $aCountries[$value->code] = $value->name;
        }

        return $aCountries;
    }

}