<?php

namespace Straker\EasyTranslationPlatform\Block\Adminhtml\Setup\LanguagePairs\Form;

use Straker\EasyTranslationPlatform\Api\Data\StrakerAPIInterface;
use Magento\Framework\View\Element\Template;
use Magento\Backend\Block\Template\Context;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Backend\Model\Session;

class Form extends Template{

    public function __construct(
        Context $context,
        StoreManagerInterface $storeManager,
        StrakerAPIInterface $strakerAPIInterface,
        Session $session,
        array $data = []
    ) {

        $this->_storeManager = $storeManager;
        $this->_strakerAPIinterface = $strakerAPIInterface;
        $this->session = $session;
        parent::__construct($context);
    }

    public function getWebsites() {
        
        return $this->_storeManager->getWebsites();
    }

    public function _getOptions(
    ){

        return $this->_strakerAPIinterface->getLanguages();
    }

    public function _formData(){

        if($this->session->getData('form_data')){

            return $this->session->getData('form_data');
        }

        return false;
    }

}