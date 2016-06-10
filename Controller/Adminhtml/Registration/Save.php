<?php

namespace Straker\EasyTranslationPlatform\Controller\Adminhtml\Registration;

use Straker\EasyTranslationPlatform\Api\Data\StrakerAPIInterface;

use Magento\Backend\App\Action;
use Magento\TestFramework\ErrorLog\Logger;
use Magento\Framework\Locale\ListsInterface;
use Magento\Config\Model\ResourceModel\Config;

class Save extends \Magento\Backend\App\Action
{

    /**
     * @param Action\Context $context
     */
    public function __construct(
        Action\Context $context,
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
        
        echo '<pre>';
        print_r($this->_StrakerAPI->callRegister($data));
        echo '</pre>';
        exit;
    }
}