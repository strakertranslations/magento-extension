<?php
/**
 *
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Straker\EasyTranslationPlatform\Controller\Adminhtml\Settings;

use Exception;
use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Framework\Controller\Result\Json;
use Straker\EasyTranslationPlatform\Api\Data\SetupInterface;
use Straker\EasyTranslationPlatform\Logger\Logger;

class DeleteTestStoreView extends Action
{
    protected $_resultJson;
    protected $_strakerSetup;
    protected $_logger;

    public $resultRedirectFactory;

    public function __construct(
        Context $context,
        Json $resultJson,
        SetupInterface $strakerSetup,
        Logger $logger
    ) {
        $this->_strakerSetup = $strakerSetup;
        $this->_logger = $logger;

        return parent::__construct($context);
    }


    public function execute()
    {
        $result = ['Success' => true] ;
        try{
            $result = $this->_strakerSetup->deleteTestingStoreView();
            if($result['Success']){
                $this->messageManager->addSuccessMessage(__('Test store view deleted successfully.'));
            }else{
                $this->messageManager->addWarningMessage($result['Message']);
            }
        } catch (Exception $e) {
            $result['Success'] = false;
            $message = __($e->getMessage());
            $result['Message'] = $message;
            $this->_logger->error($message);
        }
        return $this->_resultJson->setData($result);
    }
}
