<?php

namespace Straker\EasyTranslationPlatform\Model;

use Straker\EasyTranslationPlatform\Api\Data\SetupInterface;
use Straker\EasyTranslationPlatform\Model\Error;

use Magento\Config\Model\ResourceModel\Config;
use Magento\Store\Model\StoreManagerInterface;


class Setup extends \Magento\Framework\Model\AbstractModel implements SetupInterface
{
    protected $_configModel;
    protected $_storeManager;
    protected $_errorManager;

    public function __construct(
        Config $config,
        StoreManagerInterface $storeManagerInterface,
        Error $error
    )
    {
        $this->_configModel = $config;
        $this->_storeManager = $storeManagerInterface;
        $this->_errorManager = $error;
    }

    public function saveClientData($data){

        try{

//            $this->_configModel->SaveConfig('straker/general/first_name',$data['first_name'],'default',0);

//            $this->_configModel->SaveConfig('straker/general/last_name',$data['last_name'],'default',0);

            $this->_configModel->SaveConfig('straker/general/name',$data['first_name'].' ' . $data['last_name'],'default',0);

            $this->_configModel->SaveConfig('straker/general/email',$data['email'],'default',0);

            $this->_configModel->SaveConfig('straker/general/url',$data['url'],'default',0);

            $this->_errorManager->_error = false;

            return $this->_errorManager;

        }catch (\Exception $e){

            $this->_logger->error('error'.__FILE__.' '.__LINE__,array($e));

            $this->_errorManager->_errorMessage = 'There was an error saving your details';

            $this->_errorManager->_error = true;

            return $this->_errorManager;

        }
    }

    public function saveAppKey($appKey){

        try{

            $this->_configModel->SaveConfig('straker/general/application_key',$appKey,'default',0);

            $this->_errorManager->_error = false;

            return $this->_errorManager;

        }catch (\Exception $e){

            $this->_logger->error('error'.__FILE__.' '.__LINE__,array($e));

            $this->_errorManager->_errorMessage = 'There was an error saving your application key';

            $this->_errorManager->_error = true;

            return $this->_errorManager;

        }
    }

    public function saveAccessToken($accessToken){

        try{

            $this->_configModel->SaveConfig('straker/general/access_token',$accessToken,'default',0);

            $this->_errorManager->_error = false;

            return $this->_errorManager;

        }catch (\Exception $e){

            $this->_logger->error('error'.__FILE__.' '.__LINE__,array($e));

            $this->_errorManager->_errorMessage = 'There was an error saving your access token';

            $this->_errorManager->_error = true;

            return $this->_errorManager;

        }
    }

    public function saveStoreSetup($scopeId, $source_store, $source_language, $destination_language){

        try{

            $this->_configModel->saveConfig('straker/general/source_store',$source_store, \Magento\Store\Model\ScopeInterface::SCOPE_STORES, $scopeId);
            $this->_configModel->SaveConfig('straker/general/source_language',$source_language,\Magento\Store\Model\ScopeInterface::SCOPE_STORES, $scopeId);
            $this->_configModel->SaveConfig('straker/general/destination_language',$destination_language,\Magento\Store\Model\ScopeInterface::SCOPE_STORES,$scopeId);


            $this->_errorManager->_error = false;

            return $this->_errorManager;

        } catch (\Exception $e) {

            $this->_logger->error('error'.__FILE__.' '.__LINE__,array($e));

            $this->_errorManager->_errorMessage = 'There was an error saving Language Pairs';

            $this->_errorManager->_error = true;

            return $this->_errorManager;
        }
    }

    public function saveProductAttributes($attributes){

        try{

            if(!empty($attributes['custom'])){

                $this->_configModel->SaveConfig('straker/attributes/custom',$attributes['custom'],'default',0);
            }

            if(!empty($attributes['default'])){

                $this->_configModel->SaveConfig('straker/attributes/default',$attributes['default'],'default',0);
            }

            $this->_errorManager->_error = false;

            return $this->_errorManager;

        } catch (\Exception $e) {

            $this->_logger->error('error'.__FILE__.' '.__LINE__,array($e));

            $this->_errorManager->_errorMessage = 'There was an error saving Product Attributes';

            $this->_errorManager->_error = true;

            return $this->_errorManager;
        }
    }
}