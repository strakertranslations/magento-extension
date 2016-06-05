<?php

namespace Tym17\AdminSample\Controller\Adminhtml\SampleTwo;

use \GuzzleHttp\Client;

class Test extends \Magento\Framework\App\Action\Action
{

    public function execute()
    {
        $client = new Client(['base_uri' => 'https://test-magento-api.strakertranslations.com/']);

        $response = $client->request('GET','languages');

        $body = $response->getBody()->getContents();

        echo '<pre>';

        print_r($body);

        echo '</pre>';

        exit;
        //$url = 'https://test-magento-api.strakertranslations.com/languages';

// use key 'http' even if you send the request to https://...
//        $options = array(
//            'http' => array(
//                'header'  => "Content-type: application/x-www-form-urlencoded\r\n",
//                'method'  => 'GET'
//            )
//        );
//        $context  = stream_context_create($options);
//        $result = file_get_contents($url, false, $context);
//        if ($result === FALSE) { /* Handle error */ }
//
//        var_dump($result);
    }
}