<?php

namespace Straker\EasyTranslationPlatform\Model;

use Straker\EasyTranslationPlatform\Api\Data\RegistrationInterface;

use Straker\EasyTranslationPlatform\Helper\ConfigHelper;

use Magento\Directory\Model\Config\Source\Country;

class Registration extends \Magento\Framework\Model\AbstractModel implements RegistrationInterface
{
    /** @var  ConfigHelper */
    protected $_config;

    protected  $_countries;

    public function __construct(
        ConfigHelper $config,
        Country $countryCollection
    )
    {
        $this->_config = $config;
        $this->_countries = $countryCollection;

    }

    public function getCountryOptions()
    {

        return $this->_countries->toOptionArray();
    }
}