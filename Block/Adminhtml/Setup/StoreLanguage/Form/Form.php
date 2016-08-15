<?php

namespace Straker\EasyTranslationPlatform\Block\Adminhtml\Setup\StoreLanguage\Form;

use Staker\EasyTranslationPlatform\Api\Data\StrakerAPIInterface;
use Magento\Backend\Block\Template\Context;
use Magento\Framework\Registry;
use Magento\Framework\Data\FormFactory;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Store\Model\System\Store;

class Form extends \Magento\Backend\Block\Widget\Form\Generic
{

    public function __construct(
        Context $context,
        Registry $registry,
        FormFactory $formFactory,
        Store $systemStore,
        StrakerAPIInterface $strakerAPIInterfaceInterface,
        StoreManagerInterface $storeManager
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


//       $form->setValues($model->getData());
        $form->setUseContainer(true);
        $this->setForm($form);

        return parent::_prepareForm();
    }

    public function getWebsites() {

        return $this->_storeManager->getWebsites();
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