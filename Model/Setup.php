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
    protected $_error;

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

    public function saveAppKey($appKey){

        try{

            $this->_configModel->SaveConfig('/straker/general/application_key',$appKey,'default',0);

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

    public function saveStoreSetup($source_store, $source_language, $destination_store, $destination_language){

        try{

            $this->_configModel->SaveConfig('straker/general/source_store',$source_store,'stores',$source_store);
            $this->_configModel->SaveConfig('straker/general/source_language',$source_language,'stores',$source_store);
            $this->_configModel->SaveConfig('straker/general/destination_store',$destination_store,'stores',$source_store);
            $this->_configModel->SaveConfig('straker/general/destination_language',$destination_language,'stores',$source_store);

            $this->_errorManager->_error = false;

            return $this->_errorManager;

        } catch (\Exception $e) {

            $this->_logger->error('error'.__FILE__.' '.__LINE__,array($e));

            $this->_errorManager->_errorMessage = 'There was an error saving Language Pairs';

            $this->_errorManager->_error = true;

            return $this->_errorManager;
        }
    }
}