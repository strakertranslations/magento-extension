<?php

namespace Straker\EasyTranslationPlatform\Logger;

use Magento\Framework\HTTP\ZendClientFactory;

class Logger extends \Monolog\Logger
{
    protected $_httpClient;

    public function __construct(
        $name,
        array $handlers = array(),
        array $processors = array(),
        ZendClientFactory $clientFactory
    )
    {
        $this->_httpClient = $clientFactory;

        parent::__construct($name, $handlers, $processors);
    }

    public function _callStrakerBuglog($msg, $e)
    {
        $response = [];

        $retry = 0;

        $httpClient = $this->_httpClient->create();

        $httpClient->setParameterPost(
            [
                'APIKey'           => 'abc',
                'applicationCode'  => 'Magento Plugin',
                'HTMLReport'       => 'HTMLReport',
                'templatePath'     => $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'],
                'message'          => $msg,
                'severityCode'     => 'ERROR',
                'exceptionMessage' => $msg,
                'exceptionDetails' => $e,
                'userAgent'        => $_SERVER['HTTP_USER_AGENT'],
                'dateTime'         => date('m/d/Y H:i:s'),
                'hostName'         => $_SERVER['HTTP_HOST']


            ]
        );

        $url = 'https://uat-buglog.strakertranslations.com/bugLog/listeners/bugLogListenerREST.cfm';

        $httpClient->setUri($url);

        $httpClient->setHeaders('Content-Type:application/x-www-form-urlencoded');

        $httpClient->setConfig(['timeout' => 60, 'verifypeer' => 0]);

        $httpClient->setMethod(\Zend_Http_Client::POST);

        $httpClient->request();
    }
}
