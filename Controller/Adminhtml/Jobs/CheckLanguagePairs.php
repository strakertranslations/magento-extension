<?php

namespace Straker\EasyTranslationPlatform\Controller\Adminhtml\Jobs;

use \Exception;
use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Framework\Message\ManagerInterface;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Framework\Controller\Result\Json;

use Straker\EasyTranslationPlatform\Helper\ConfigHelper;
use Straker\EasyTranslationPlatform\Helper\Data;
use Straker\EasyTranslationPlatform\Logger\Logger;


class CheckLanguagePairs extends Action
{

    protected $_messageManager;
    protected $_storeManager;
    protected $_resultJson;
    protected $_configHelper;
    protected $_logger;
    protected $_dataHelper;

    public $resultRedirectFactory;

    public function __construct(
        Context $context,
        Json $resultJson,
        ManagerInterface $messageManager,
        StoreManagerInterface $storeManager,
        ConfigHelper $configHelper,
        Logger $logger,
        Data $data
    )
    {
        $this->_messageManager = $messageManager;
        $this->_storeManager = $storeManager;
        $this->_resultJson = $resultJson;
        $this->_configHelper = $configHelper;
        $this->_logger = $logger;
        $this->_dataHelper = $data;

        return parent::__construct($context);
    }


    public function execute()
    {

        $target_store_id = $this->getRequest()->getPost('target_store_id');

        $store_data = $this->_configHelper->getStoreInfo($target_store_id);

        $result['store_data'] = $store_data;

        if(empty($store_data['straker/general/source_store']))
        {
            $result['success'] = false;

            $result['redirect_url'] = $this->_dataHelper->getUrl('EasyTranslationPlatform/Setup_LanguagePairs/Index',['target_store_id'=>$target_store_id]);

        }else{

            $result['success'] = true;

        }

        $result['target_store_id'] = $target_store_id;

        return $this->_resultJson->setData($result);
    }
}