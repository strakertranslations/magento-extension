<?php
class StrakerTranslations_EasyTranslationPlatform_Model_Api extends Mage_Core_Model_Abstract
{
    /**
     * Error codes recollected after each API call
     *
     * @var array
     */
    protected $_callErrors = array();

    protected $_storeId;

    /**
     * Headers for each API call
     *
     * @var array
     */
    protected $_headers = array();

    protected $_options = array();

    protected function _construct()
    {

        $this->_storeId = ($this->getStore()) ? $this->getStore() : 0;
        $this->_init('strakertranslations_easytranslationplatform/api');
        $this->_headers[] = 'Authorization: Bearer '. Mage::getStoreConfig('straker/general/access_token', $this->_storeId);
        $this->_headers[] = 'X-Auth-App: '. Mage::getStoreConfig('straker/general/application_key', $this->_storeId);
    }

    protected  function _call($url, $method = 'get', array $request = array(), $raw = false)
    {
        switch ($method) {
            case 'post':
                $method =  Zend_Http_Client::POST;
                break;

            case 'get' :
                $method =  Zend_Http_Client::GET;
                break;
        }

        try {
            $http = new Varien_Http_Adapter_Curl();
            $config = array(
              'timeout'    => 60,
              'verifypeer' => 0,
            );

            $http->setConfig($config);
            $http->write(
              $method,
              $url,
              '1.1',
              $this->_headers,
              $request //$this->_buildQuery($request)
            );
            $response = $http->read();

        } catch (Exception $e) {
            $debugData['http_error'] = array('error' => $e->getMessage(), 'code' => $e->getCode());
            $this->_debug($debugData);
            throw $e;
        }

        $debugData['response'] = $response;


        // handle transport error
        if ($http->getErrno()) {
            Mage::logException(new Exception(
              sprintf('CURL connection error #%s: %s', $http->getErrno(), $http->getError())
            ));
            $http->close();

            Mage::throwException(Mage::helper('strakertranslations_easytranslationplatform')->__('Unable to communicate with the Straker Translations Api.'));
        }

        // cUrl resource must be closed after checking it for errors
        $http->close();

        $response = preg_split('/^\r?$/m', $response, 2);
        $response = trim($response[1]);

        if ($raw) {
            return $response;
        }

        $response = json_decode($response);
        $this->_callErrors = array();
        if ($this->_isCallSuccessful($response)) {
            return $response;
        }
        $this->_handleCallErrors($response);
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

    protected  function _debug($debugData){

        // to be added

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
     * @throws Mage_Core_Exception
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
        return Mage::getStoreConfig('straker/api_url/register');
    }

    protected  function _getLanguagesUrl(){
        return Mage::getStoreConfig('straker/api_url/languages');
    }

    protected  function _getCountriesUrl(){
        return Mage::getStoreConfig('straker/api_url/countries');
    }

    protected  function _getTranslateUrl(){
        /** @var  $helper StrakerTranslations_EasyTranslationPlatform_Helper_Data */
        $helper = Mage::helper('strakertranslations_easytranslationplatform');
        if( $helper->isSandboxMode() ){
            return Mage::getStoreConfig('straker/api_url/translate_sandbox');
        }else{
            return Mage::getStoreConfig('straker/api_url/translate');
        }
    }

    protected  function _getQuoteUrl(){
        return Mage::getStoreConfig('straker/api_url/quote');
    }

    protected  function _getPaymentUrl(){
        return Mage::getStoreConfig('straker/api_url/payment');
    }

    protected  function _getSupportUrl(){
        return Mage::getStoreConfig('straker/api_url/support');
    }

    public function callRegister($data){
        return $this->_call($this->_getRegisterUrl(), 'post', $data);
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

//    public function getCountries(){
//        $result = $this->_call($this->_getCountiresUrl());
//        return $result->country ? $result->country : false;
//    }
//
//    public function getLanguages(){
//        $result = $this->_call($this->_getLanguagesUrl());
//        return $result->languages ? $result->languages : false;
//    }

    public function getCountries()
    {
        /** @var  $helper StrakerTranslations_EasyTranslationPlatform_Helper_Data */
        $helper = Mage::helper('strakertranslations_easytranslationplatform');
        $filePath = $helper->getDataFilePath();
        $fileName = 'countries.json';

        if (!file_exists($filePath)) {
            mkdir($filePath, 0777, true);
        }

        $fileFullPath = $filePath . DIRECTORY_SEPARATOR . $fileName;

        if (file_exists($fileFullPath)) {
            $result = json_decode(file_get_contents($fileFullPath));
        } else {
            $result = $this->_call($this->_getCountriesUrl());
            if (!empty($result)) {
                file_put_contents($fileFullPath, json_encode($result));
            }
        }
        return isset($result->country) ?  $result->country : [];
    }

    public function getLanguages()
    {
        /** @var  $helper StrakerTranslations_EasyTranslationPlatform_Helper_Data */
        $helper = Mage::helper('strakertranslations_easytranslationplatform');
        $filePath = $helper->getDataFilePath();
        $fileName = 'languages.json';

        if (!file_exists($filePath)) {
            mkdir($filePath, 0777, true);
        }

        $fileFullPath = $filePath . DIRECTORY_SEPARATOR . $fileName;

        if (file_exists($fileFullPath)) {
            $result = json_decode(file_get_contents($fileFullPath));
        } else {
            $result = $this->_call($this->_getLanguagesUrl());
            if (!empty($result)) {
                file_put_contents($fileFullPath, json_encode($result));
            }
        }
        return isset($result->languages) ? $result->languages : [];
    }

    public function _getLanguageName($code = '')
    {
        $languages = $this->getLanguages();
        $languageName = '';
        $isArray = is_array($code) ? true : false;
        foreach ($languages as $k => $val) {
            if( $isArray ){
                if ( ($key = array_search($val->code,  $code)) !== false ) {
                    $languageName[$val->code] = $val->name;
                    unset($code[$key]);
                }else{
                    continue;
                }
                if( count($code) <= 0 ){
                    break;
                }
            }else{
                if ($val->code == $code) {
                    $languageName = $val->name;
                    break;
                }
            }
        }
        return $languageName;
    }

    public function saveAppKey($appKey){
        if ($this->_storeId === 0) {
            Mage::getModel('core/config')->saveConfig('straker/general/application_key', $appKey);
        }
        else {
            Mage::getModel('core/config')->saveConfig('straker/general/application_key', $appKey, 'website', $this->_storeId);
        }
    }

    public function saveAccessToken($accessToken){
        if ($this->_storeId === 0) {
            Mage::getModel('core/config')->saveConfig('straker/general/access_token', $accessToken);
        }
        else {
            Mage::getModel('core/config')->saveConfig('straker/general/access_token', $accessToken, 'website', $this->_storeId);
        }
    }

}