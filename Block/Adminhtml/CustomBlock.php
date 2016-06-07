<?php
namespace Tym17\AdminSample\Block\Adminhtml;

use Magento\Backend\Block\Template;
use Tym17\AdminSample\Helper\ConfigHelper;
use Magento\Store\Model\StoreManagerInterface;

class CustomBlock extends Template
{
    /**
     * @var \Tym17\AdminSample\Helper\ConfigHelper
     */
    protected $_config;

    protected $_adminSampleModel;

    protected  $_storeManager;

    /**
    * @param Context $context
    * @param array $data
    */
    public function __construct(
        Template\Context $context,
//        AdminSample $adminSampleModel,
        AdminSampleInterface $adminSampleModel,
        ConfigHelper $config,
        StoreManagerInterface $storeManager,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->_config = $config;
        $this->_adminSampleModel = $adminSampleModel;
        $this->_storeManager = $storeManager;
    }

    /**
     * @return string
     */
    public function greet()
    {
        //return $this->_adminSampleModel->getGreetings();
        return $this->_adminSampleModel->getName();
    }

    public function getSampleText()
    {
        return $this->_adminSampleModel->getSampleText();
    }

    public function getHeading()
    {
        return $this->_adminSampleModel->getHeading();
    }

    public function getWebsites() {
        return false;
    }

}
