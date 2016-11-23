<?php

namespace Straker\EasyTranslationPlatform\Controller\Adminhtml\Setup\LanguagePairs;

use Straker\EasyTranslationPlatform\Api\Data\SetupInterface;
use Straker\EasyTranslationPlatform\Logger\Logger;

use Magento\Framework\App\Action\Context;

class Save extends \Magento\Backend\App\Action
{

    public function __construct(
        Context $context,
        SetupInterface $setupInterface,
        Logger $logger
    ) {
    

        parent::__construct($context);

        $this->_setup = $setupInterface;
        $this->_logger = $logger;
    }


    public function execute()
    {

        $data = $this->getRequest()->getParams();

        $resultRedirect = $this->resultRedirectFactory->create();

        if ($data) {
            $data = $this->sortData($data);

            try {
                foreach ($data as $key => $value) {
                    $this->_setup->saveStoreSetup(substr($key, -1), $data[substr($key, -1)]['magento_source_store_id_'.substr($key, -1)], $data[substr($key, -1)]['straker_source_language_store_id_'.substr($key, -1)], $data[substr($key, -1)]['straker_target_language_store_id_'.substr($key, -1)]);
                }

                $resultRedirect->setPath('/Setup_productattributes/index/');

                return $resultRedirect;
            } catch (\Magento\Framework\Exception\LocalizedException $e) {
                $this->_logger->error('error'.__FILE__.' '.__LINE__, [$e]);

                $this->messageManager->addError($e->getMessage());
            } catch (\RuntimeException $e) {
                $this->_logger->error('error'.__FILE__.' '.__LINE__, [$e]);

                $this->messageManager->addError($e->getMessage());
            } catch (\Exception $e) {
                $this->_logger->error('error'.__FILE__.' '.__LINE__, [$e]);

                $this->messageManager->addException($e, __('Something went wrong while saving the language configuration.'));
            }

            $resultRedirect->setPath('*/*/index/');
        }

        return $resultRedirect;
    }

    private function sortData($data)
    {

        $language_pair_data = [];

        foreach ($data as $key => $value) {
            if (ctype_digit(substr($key, -1))) {
                $language_pair_data[substr($key, -1)][$key] = $value;
            };
        }

        return $language_pair_data;
    }
}
