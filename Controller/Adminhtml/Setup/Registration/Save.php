<?php

namespace Straker\EasyTranslationPlatform\Controller\Adminhtml\Setup\Registration;

use Straker\EasyTranslationPlatform\Api\Data\StrakerAPIInterface;
use Straker\EasyTranslationPlatform\Api\Data\SetupInterface;
use Straker\EasyTranslationPlatform\Logger\Logger;

use Magento\Config\Model\ResourceModel\Config;
use Magento\Framework\App\Config\ReinitableConfigInterface;
use Magento\Backend\App\Action\Context;

class Save extends \Magento\Backend\App\Action
{

    protected $_config;
    protected $_reinitConfig;
    protected $_strakerAPI;
    protected $_setup;
    protected $_logger;

    public function __construct(
        Context $context,
        Config $config,
        ReinitableConfigInterface $reinitableConfigInterface,
        StrakerAPIInterface $strakerAPIInterface,
        SetupInterface $setupInterface,
        Logger $logger
    ) {
    
        $this->_config = $config;
        $this->_reinitConfig = $reinitableConfigInterface;
        $this->_strakerAPI = $strakerAPIInterface;
        $this->_setup = $setupInterface;
        $this->_logger = $logger;

        parent::__construct($context);
    }

    public function execute()
    {

        $data = $this->getRequest()->getParams();

        $resultRedirect = $this->resultRedirectFactory->create();

        if ($data) {
            try {
                $this->_setup->saveClientData($data);

                $oRegistration = $this->_strakerAPI->callRegister($data);

                $this->_setup->saveAccessToken($oRegistration->access_token);

                $this->_setup->saveAppKey($oRegistration->application_key);

                $this->_reinitConfig->reinit();

                $resultRedirect->setPath('/Setup_productattributes/index/');

                return $resultRedirect;
            } catch (\Magento\Framework\Exception\LocalizedException $e) {
                    $this->_logger->error('error'.__FILE__.' '.__LINE__.'', [$e]);

                    $resultRedirect->setPath('/*/index/');

                    $this->messageManager->addError('There was an error registering your details');

                    return $resultRedirect;
            } catch (\RuntimeException $e) {
                $this->_logger->error('error'.__FILE__.' '.__LINE__, [$e]);

                $this->messageManager->addError($e->getMessage());
            } catch (\Exception $e) {
                $this->_logger->error('error'.__FILE__.' '.__LINE__, [$e]);

                $this->messageManager->addException($e, __('There was an error registering your details'));
            }

            $resultRedirect->setPath('/*/index/');
        }

        return $resultRedirect;
    }
}
