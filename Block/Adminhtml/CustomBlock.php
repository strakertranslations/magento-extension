<?php
namespace Straker\EasyTranslationPlatform\Block\Adminhtml;

use Magento\Backend\Block\Template;
use Straker\EasyTranslationPlatform\Helper\ConfigHelper;
use Magento\Store\Model\StoreManagerInterface;
use Straker\EasyTranslationPlatform\Api\Data\EasyTranslationPlatformInterface;

class CustomBlock extends Template
{
    /**
     * @var \Straker\EasyTranslationPlatform\Helper\ConfigHelper
     */
    protected $_config;

    protected $_easyTranslationModel;

    protected  $_storeManager;

    /**
    * @param Context $context
    * @param array $data
    */
    public function __construct(
        Template\Context $context,
        EasyTranslationPlatformInterface $easyTranslationPlatformInterface,
        ConfigHelper $config,
        StoreManagerInterface $storeManager,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->_config = $config;
        $this->_easyTranslationModel = $easyTranslationPlatformInterface;
        $this->_storeManager = $storeManager;
    }

    /**
     * @return string
     */
    public function greet()
    {
        return $this->_easyTranslationModel->getName();
    }

    public function getSampleText()
    {
        return $this->_easyTranslationModel->getSampleText();
    }

    public function getHeading()
    {
        return $this->_easyTranslationModel->getHeading();
    }

    public function getWebsites() {
        return false;
    }

}
