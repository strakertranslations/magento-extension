<?php

namespace Straker\EasyTranslationPlatform\Block\Adminhtml\Setup\LanguagePairs\Form;

use Straker\EasyTranslationPlatform\Api\Data\StrakerAPIInterface;
use Magento\Framework\View\Element\Template;
use Magento\Backend\Block\Template\Context;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Backend\Model\Session;
use Straker\EasyTranslationPlatform\Helper\ConfigHelper;
use Magento\Framework\Locale\ListsInterface;
use Magento\Framework\App\RequestInterface;

class Form extends Template{


    protected $_storeInfoData;
    protected $_request;

    public function __construct(
        Context $context,
        StoreManagerInterface $storeManager,
        StrakerAPIInterface $strakerAPIInterface,
        Session $session,
        ConfigHelper $configHelper,
        ListsInterface $localeList,
        RequestInterface $request,
        array $data = []
    ) {

        $this->_storeManager = $storeManager;
        $this->_strakerAPIinterface = $strakerAPIInterface;
        $this->session = $session;
        $this->configHelper = $configHelper;
        $this->localeList = $localeList;
        $this->_request = $request;
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

    public function getStoreInfo($storeId)
    {

        $storeData = $this->configHelper->getStoreInfo($storeId);

        $this->_storeInfoData = $storeData;

        return $storeData;
    }

    public function getTranslationLanguage()
    {

        return $this->_storeInfoData['straker/general/destination_language'];
    }

    public function getSourceLanguage()
    {
        return $this->_storeInfoData['straker/general/source_language'];

    }

    public function getSourceStore()
    {
        return $this->_storeInfoData['straker/general/source_store'];
    }

    public function getMessage($store_id)
    {
        if($this->_request->get('target_store_id') && $this->_request->get('target_store_id') == $store_id ){

            return true;
        };

        return false;
    }

}