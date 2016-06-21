<?php

namespace Straker\EasyTranslationPlatform\Block\Adminhtml\Setup\LanguagePairs\Form;

use Straker\EasyTranslationPlatform\Api\Data\StrakerAPIInterface;
use Magento\Framework\View\Element\Template;
use Magento\Backend\Block\Template\Context;
use Magento\Store\Model\StoreManagerInterface;

class Form extends Template{

    public function __construct(
        Context $context,
        StoreManagerInterface $storeManager,
        StrakerAPIInterface $strakerAPIInterface,
        array $data = []
    ) {

        $this->_storeManager = $storeManager;
        $this->_strakerAPIinterface = $strakerAPIInterface;
        parent::__construct($context);
    }

    public function getWebsites() {

        return $this->_storeManager->getWebsites();
    }

    public function _getOptions(
    ){

        return $this->_strakerAPIinterface->getLanguages();
    }

}