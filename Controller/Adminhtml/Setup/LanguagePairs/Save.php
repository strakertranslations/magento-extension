<?php

namespace Straker\EasyTranslationPlatform\Controller\Adminhtml\Setup\LanguagePairs;

use Straker\EasyTranslationPlatform\Api\Data\SetupInterface;
use Straker\EasyTranslationPlatform\Model\Error;

use Magento\Framework\App\Action\Context;

class Save extends \Magento\Backend\App\Action
{

    public function __construct(
        Context $context,
        SetupInterface $setupInterface,
        Error $error
    )
    {

        parent::__construct($context);

        $this->_setup = $setupInterface;
        $this->_errorManager = $error;
    }


    public function execute()
    {

        $data = $this->getRequest()->getParams();

        $resultRedirect = $this->resultRedirectFactory->create();

        if ($data) {

            try {

                $this->_setup->saveStoreSetup($data['destination_store'], $data['source_store'],$data['source_language'],$data['destination_language']);

                if($this->_errorManager->_error){

                    $this->_getSession()->setFormData($data);

                    $resultRedirect->setPath('*/*/index/');

                    $this->messageManager->addError($this->_errorManager->getErrorMessage());

                }else{

                    $resultRedirect->setPath('*/Jobs/new');

                }

                return $resultRedirect;


            } catch (\Magento\Framework\Exception\LocalizedException $e) {

                $this->_logger->error('error'.__FILE__.' '.__LINE__,array($e));

                $this->messageManager->addError($e->getMessage());

            } catch (\RuntimeException $e) {

                $this->_logger->error('error'.__FILE__.' '.__LINE__,array($e));

                $this->messageManager->addError($e->getMessage());

            } catch (\Exception $e) {

                $this->_logger->error('error'.__FILE__.' '.__LINE__,array($e));

                $this->messageManager->addException($e, __('Something went wrong while saving the language configuration.'));
            }

            $resultRedirect->setPath('*/*/index/');

        }

        return $resultRedirect;
    }
}