<?php

namespace Straker\EasyTranslationPlatform\Controller\Adminhtml\Registration;

use Straker\EasyTranslationPlatform\Api\Data\StrakerAPIInterface;

use Magento\Backend\App\Action;
use Magento\TestFramework\ErrorLog\Logger;
use Magento\Framework\Locale\ListsInterface;
use Magento\Config\Model\ResourceModel\Config;
use Magento\Framework\App\Action\Context;

class Save extends \Magento\Backend\App\Action
{

    /**
     * @param Action\Context $context
     */
    public function __construct(
        Context $context,
        Config $config,
        StrakerAPIInterface $strakerAPIInterface
    )
    {
        $this->_config = $config;
        $this->_StrakerAPI = $strakerAPIInterface;

        parent::__construct($context);
    }

    /**
     * {@inheritdoc}
     */
    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed('Straker_EasyTranslationPlatform::save');
    }


    public function execute()
    {
        $data = $this->getRequest()->getPostValue();

        $oRegistration = $this->_StrakerAPI->callRegister($data);

        $this->_StrakerAPI->saveAccessToken($oRegistration->access_token);

        $this->_StrakerAPI->saveAppKey($oRegistration->application_key);

        echo 'Success';

        exit;
    }
}