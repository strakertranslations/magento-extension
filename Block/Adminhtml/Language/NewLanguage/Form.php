<?php

namespace Straker\EasyTranslationPlatform\Block\Adminhtml\Language\NewLanguage;

use Straker\EasyTranslationPlatform\Api\Data\StrakerAPIInterface;
use Magento\Framework\View\Element\Template;
use Magento\Backend\Block\Template\Context;
use Magento\Store\Model\StoreManagerInterface;

class Form extends Template{

    public function __construct(
        Context $context,
        StoreManagerInterface $storeManager,
        array $data = []
    ) {
        $this->_storeManager = $storeManager;
        parent::__construct($context);

    }

    public function getWebsites() {

        return $this->_storeManager->getWebsites();
    }

    public function getFormActionUrl()
    {
        if ($this->hasFormActionUrl()) {
            return $this->getData('form_action_url');
        }
        return $this->getUrl('*/*/save');
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