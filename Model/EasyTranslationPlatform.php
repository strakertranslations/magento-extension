<?php

namespace Straker\EasyTranslationPlatform\Model;

use Straker\EasyTranslationPlatform\Api\Data\EasyTranslationPlatformInterface;
use Straker\EasyTranslationPlatform\Helper\ConfigHelper;
use Magento\Framework\Locale\ListsInterface;

class EasyTranslationPlatform extends \Magento\Framework\Model\AbstractModel implements EasyTranslationPlatformInterface
{
    /** @var  ConfigHelper */
    protected $_config;

    protected  $_listsInterface;

    public function __construct(
        ConfigHelper $config,
        ListsInterface $listsInterface
    )
    {
        $this->_config = $config;
        $this->_listsInterface = $listsInterface;

    }
    public function getGreetings()
    {
        return 'Greetings!';
    }

    public function getSampleText()
    {
        return $this->_config->getConfig('txt/textsample');
    }

    public function getName()
    {
        return 'Rakesh';
    }

    public function getHeading()
    {
        return $this->_config->getConfig('txt/heading');
    }

    public function getLocaleOptions()
    {
        return $this->_listsInterface->getOptionLocales();
    }
}