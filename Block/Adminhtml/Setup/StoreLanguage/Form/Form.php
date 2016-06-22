<?php

namespace Straker\EasyTranslationPlatform\Block\Adminhtml\Setup\StoreLanguage\Form;

use Staker\EasyTranslationPlatform\Api\Data\StrakerAPIInterface;
use Magento\Backend\Block\Template\Context;
use Magento\Framework\Registry;
use Magento\Framework\Data\FormFactory;
use Magento\Store\Model\StoreManagerInterface;

class Form extends \Magento\Backend\Block\Widget\Form\Generic
{

    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\Data\FormFactory $formFactory,
        \Magento\Store\Model\System\Store $systemStore,
        \Straker\EasyTranslationPlatform\Api\Data\StrakerAPIInterface $strakerAPIInterfaceInterface,
        \Magento\Store\Model\StoreManagerInterface $storeManager
    ) {

        $this->_systemStore = $systemStore;
        $this->_strakerAPI = $strakerAPIInterfaceInterface;
        $this->_storeManager = $storeManager;
        parent::__construct($context, $registry, $formFactory);
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