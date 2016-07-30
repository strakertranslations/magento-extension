<?php

namespace Straker\EasyTranslationPlatform\Model;

use Magento\Framework\Model\Context;
use Magento\Framework\Registry;
use Exception;
use Straker\EasyTranslationPlatform\Api\Data\StrakerAPIInterface;
use Straker\EasyTranslationPlatform\Helper\ConfigHelper;
use Straker\EasyTranslationPlatform\Logger\Logger;
use Straker\EasyTranslationPlatform\Model\Error;

use Magento\Framework\HTTP\ZendClientFactory;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Config\Model\ResourceModel\Config;

class StrakerAPI extends \Magento\Framework\Model\AbstractModel implements StrakerAPIInterface
{

    protected $_config;

    /**
     * Error codes recollected after each API call
     *
     * @var array
     */
    protected $_callErrors = [];

    protected $_storeId;

    protected $_logger;

    /**
     * Headers for each API call
     *
     * @var array
     */
    protected $_headers = [];

    protected $_options = [];
    protected $_configHelper;
    protected $_configModel;
    protected $_httpClient;
    protected $_errorManager;
    protected $_storeManager;

    public function __construct(
        Context $context,
        Registry $registry,
        ConfigHelper $configHelper,
        Config $configModel,
        ZendClientFactory $httpClient,
        Logger $logger,
        StoreManagerInterface $storeManagerInterface,
        Error $error
    )
    {
        parent::__construct( $context, $registry );
        $this->_configHelper = $configHelper;
        $this->_configModel = $configModel;
        $this->_httpClient = $httpClient;
        $this->_logger = $logger;
        $this->_storeManager = $storeManagerInterface;
        $this->_errorManager = $error;
//        $this->_storeId = ($this->getStore()) ? $this->getStore() : 0;
//        $this->_init('strakertranslations_easytranslationplatform/api');
//        $this->_headers[] = 'Authorization: Bearer '. Mage::getStoreConfig('straker/general/access_token', $this->_storeId);
//        $this->_headers[] = 'X-Auth-App: '. Mage::getStoreConfig('straker/general/application_key', $this->_storeId);
    }

    protected  function _call($url, $method = 'get', array $request = array(), $raw = false)
    {

        $response = [];

        $retry = 0;

        $httpClient = $this->_httpClient->create();

        switch ($method) {

            case 'post':

                $method =  \Zend_Http_Client::POST;

                $httpClient->setParameterPost($request);

                if($request['source_file']){

                    $httpClient->setFileUpload($request['source_file'],'source_file');
                }

                break;

            case 'get' :

                $method =  \Zend_Http_Client::GET;

                break;
        }

        $httpClient->setUri($url);

        $httpClient->setConfig(['timeout' => 60,'verifypeer'=>0]);

        $httpClient->setHeaders($this->getHeaders());

        $httpClient->setMethod($method);


        try {

            $response = $httpClient->request()->getBody();

            $debugData['response'] = $response;

            return json_decode($response);

        } catch (Exception $e) {

            $debugData['http_error'] = array('error' => $e->getMessage(), 'code' => $e->getCode());

            throw $e;
        }

        return $response;
    }

    protected function _buildQuery($request)
    {
        return http_build_query($request);
    }

    /**
     * Set array of additional cURL options
     *
     * @param array $options
     * @return Varien_Http_Adapter_Curl
     */
    public function setOptions(array $options = array())
    {
        $this->_options = $options;
        return $this;
    }
    

    protected function _isCallSuccessful($response)
    {
        if (isset($response->code)) {
            return false;
        }

        if (isset($response->success) || isset($response->languages) || isset($response->country)) {
            return true;
        }
        return false;
    }

    /**
     * Handle logical errors
     *
     * @param array $response
     * @throst Mage_Core_Exception
     */
    protected function _handleCallErrors($response)
    {
        if(empty($response)){
            return;
        }
        if (isset($response->message) && strpos($response->message,'Authentication failed') !== false){
            $response->magentoMessage = $response->message;
        }
        return;
// to be added
    }

    protected  function _getRegisterUrl(){
        return $this->_configHelper->getRegisterUrl();
    }

    protected  function _getLanguagesUrl(){
        return $this->_configHelper->getLanguagesUrl();
    }

    protected  function _getCountriesUrl(){

        return $this->_configHelper->getCountriesUrl();
    }

    protected  function _getTranslateUrl(){
        return $this->_configHelper->getTranslateUrl();
    }

    protected  function _getQuoteUrl(){
        return $this->_configHelper->getQuoteUrl();
    }

    protected  function _getPaymentUrl(){
        return $this->_configHelper->getPaymentUrl();
    }

    protected  function _getSupportUrl(){
        return $this->_configHelper->getSupportUrl();
    }

    public function getHeaders(){

        $this->_headers[] = 'Authorization: Bearer '.$this->_configHelper->getAccessToken();
        $this->_headers[] = 'X-Auth-App: '. $this->_configHelper->getApplicationKey();

        return $this->_headers;
    }


    public function callRegister($data){

        try{

            return $this->_call($this->_getRegisterUrl(), 'post', $data);

        }catch (\Exception $e){

            $this->_logger->error('error',__FILE__.' '.__LINE__.''.$e,$e);

            $this->_errorManager->_errorMessage = 'There was an error registering your details';

            $this->_errorManager->_error = true;

            return $this->_errorManager;
        }

    }

    public function callTranslate($data){
        $this->_headers[] = 'Content-Type:multipart/form-data';
        return $this->_call($this->_getTranslateUrl(), 'post', $data);
    }

    public function callSupport($data){
        return $this->_call($this->_getSupportUrl(), 'post', $data);
    }

    public function getQuote($data){
        return $this->_call($this->_getQuoteUrl().'?'. $this->_buildQuery($data));
    }

    public function getPayment($data){
        return $this->_call($this->_getPaymentUrl().'?'. $this->_buildQuery($data));
    }

    public function getTranslation($data){
        return $this->_call($this->_getTranslateUrl().'?'. $this->_buildQuery($data));
    }

    public function getTranslatedFile($downloadUrl){
        return $this->_call($downloadUrl,'get',array(),true);
    }

    public function getCountries(){

        $result = $this->_call($this->_getCountriesUrl());

        return $result->country;
    }

    public function getLanguages(){
        $result = $this->_call($this->_getLanguagesUrl());
        return $result->languages ? $result->languages : false;
    }

}