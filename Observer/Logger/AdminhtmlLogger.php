<?php

namespace Straker\EasyTranslationPlatform\Observer\Logger;

use Magento\Framework\Event\ObserverInterface;
use Straker\EasyTranslationPlatform\Logger\Logger;

class AdminhtmlLogger implements ObserverInterface
{

    public function __construct(
        Logger $logger

    ) {
        $this->_logger = $logger;
    }

    public function execute(\Magento\Framework\Event\Observer $observer)
    {

        $this->_logger->debug('helloWorld');

        return $this;

    }
}