<?php

namespace Straker\EasyTranslationPlatform\Helper;

use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Framework\App\Helper\Context;
use Magento\Framework\DomDocument\DomDocumentFactory;

class XmlHelper extends AbstractHelper
{
    /** @var string $_version */
    private $_version = "1.0";

    /** @var string $_encoding */
    private $_encoding = 'utf-8';

    /** @var string $_xmlFilePath */
    private $_xmlFilePath;

    /** @var string $_xmlFileName */
    private $_xmlFileName;

    /** @var \DOMDocument $_xmlFileName */
    private $_dom;

    /** @var \DOMElement $_xmlFileName */
    private $_root;

    /** @var \DOMElement $_xmlFileName */
    private $_data;

    /** @var JobHelper $_jobHelper */
    private $_configHelper;

    private $_elemAttributes = [
        'name',
        'content_context',
        'content_context_url',
        'product_id',
        'attribute_id',
        'parent_attribute_id',
        'parent_attribute_name',
        'option_id'
    ];

    public function __construct(
        Context $context,
        ConfigHelper $configHelper,
        DomDocumentFactory $domDocumentFactory
    ) {
        $this->_dom = $domDocumentFactory->create();
        $this->_configHelper = $configHelper;
        $this->_xmlFilePath = $this->_configHelper->getOriginalXMLFilePath();
        parent::__construct($context);
    }

    /**
     * @param $jobId
     * @return bool|\DOMElement
     */
    public function create( $jobId ){
        $this->_dom->version = $this->getVersion();
        $this->_dom->encoding = $this->getEncoding();

        $this->_xmlFileName = $this->_xmlFilePath . DIRECTORY_SEPARATOR . 'straker_job'. $jobId .'.xml';
        $flag = true;

        if( !file_exists( $this->_xmlFilePath ) ){
            $flag = mkdir( $this->_xmlFilePath, 0777, true );
        }

        if( !$flag ) {
            return false;
        }

        if( !file_exists( $this->_xmlFileName ) ){
            $isSuccess = file_put_contents( $this->_xmlFileName, "" );
            if( $isSuccess === false ){
                return false;
            }
        }

        $this->_root = $this->_dom->createElement('root');
        return true;
    }


    /**
     * @param array $attributes
     * @return bool
     */
    public function appendDataToRoot( $attributes = [] ){


        $this->_data = $this->_dom->createElement( 'data' );

        foreach ($attributes as $key => $value){

            ($key !='value')? $this->_data->setAttribute($key, $value) : false;
        }

        $valueElem = $this->_dom->createElement( 'value' );
        $valueElem->appendChild( $this->_dom->createCDATASection( $attributes['value'] ) );

        $this->_data->appendChild( $valueElem );
        $this->_root->appendChild( $this->_data );

        return true;
    }

    /**
     * @param array $data
     * @return bool
     */
    private function _validateKeys( $data = [] ){
        return ( 0 === count( array_diff( $this->_elemAttributes, array_keys( $data ) ) ) );
    }
    
    /**
     * @return bool
     */
    public function saveXmlFile()
    {
        $this->_dom->formatOutput = true;
        if( !file_exists( $this->_xmlFileName )){
            return false;
        }
        $this->_dom->appendChild( $this->_root );
        $saveData = $this->_dom->save( $this->_xmlFileName );
        //var_dump($saveData);
        //exit;
        $this->_dom->documentElement->parentNode->removeChild($this->_root);

        return true;
    }

    /**
     * @return string
     */
    public function getVersion()
    {
        return $this->_version;
    }

    /**
     * @param string $version
     */
    public function setVersion($version)
    {
        $this->_version = $version;
    }

    /**
     * @return string
     */
    public function getEncoding()
    {
        return $this->_encoding;
    }

    /**
     * @param string $encoding
     */
    public function setEncoding($encoding)
    {
        $this->_encoding = $encoding;
    }

    /**
     * @return string
     */
    public function getXmlFilePath()
    {
        return $this->_xmlFilePath;
    }

    /**
     * @return string
     */
    public function getXmlFileName()
    {
        return $this->_xmlFileName;
    }

    /**
     * @return \DOMElement
     */
    public function getRoot()
    {
        return $this->_root;
    }

    /**
     * @return array
     */
    public function getElemAttributes()
    {
        return $this->_elemAttributes;
    }

}