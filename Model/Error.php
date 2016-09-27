<?php

namespace Straker\EasyTranslationPlatform\Model;


class Error extends \Magento\Framework\Model\AbstractModel
{
    public $_error;

    public  $_errorMessage;

    public function __construct()
    {
    }

    public function getErrorMessage()
    {
        return $this->_errorMessage;
    }

    public function isError()
    {
        return $this->_error;
    }
}