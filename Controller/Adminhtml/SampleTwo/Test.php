<?php

namespace Tym17\AdminSample\Controller\Adminhtml\SampleTwo;

use \GuzzleHttp\Client;
use Magento\Store\Model\StoreManagerInterface;
use Tym17\AdminSample\Api\Data\AdminSampleInterface;
use Magento\Framework\App\Action\Context;

class Test extends \Magento\Framework\App\Action\Action
{

    protected $_storeManager;

    public function __construct(
        Context $context,
        StoreManagerInterface $storeManagerInterface,
        AdminSampleInterface $adminSampleInterface
    )
    {
        $this->_adminManager = $adminSampleInterface;
        $this->_storeManager = $storeManagerInterface;
        return parent::__construct($context);
    }

    public function execute()
    {
        $a = [];

        foreach($this->_adminManager->getLocaleOptions() as $key => $value)
        {
            $a[$value['value']] = $value['label'];
        }

        echo '<pre>';
        print_r($a);
        echo '</pre>';
        exit;
    }
}