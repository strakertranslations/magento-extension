<?php

namespace Straker\EasyTranslationPlatform\Controller\Adminhtml\Test;

use Magento\Backend\App\Action\Context;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\View\Result\PageFactory;
use Magento\Framework\Controller\Result\JsonFactory;
use Magento\Eav\Api\AttributeRepositoryInterface;
use Magento\Eav\Model\ResourceModel\Entity\Attribute\Collection;
use Magento\Framework\Xml\Parser;


use Straker\EasyTranslationPlatform\Helper\ConfigHelper;
use Straker\EasyTranslationPlatform\Helper\XmlHelper;

class Index extends \Magento\Backend\App\Action
{
    protected $_attributeCollection;
    protected $_jsonFactory;
    protected $_resultPageFactory;
    protected $_configHelper;
    protected $_xmlHelper;

    public function __construct(
        Context $context,
        PageFactory $pageFactory,
        JsonFactory $jsonFactory,
        Collection $attCollection,
        ConfigHelper $configHelper,
        XmlHelper $xmlHelper,
        Parser $xmlParser
    )
    {
        $this->_attributeCollection = $attCollection;
        $this->_resultPageFactory = $pageFactory;
        $this->_jsonFactory = $jsonFactory;
        $this->_configHelper = $configHelper;
        $this->_xmlHelper = $xmlHelper;
        $this->_xmlParser = $xmlParser;

        return parent::__construct($context);
    }

    public function execute()
    {

        $filePath = str_replace('job-file','translated-file',$this->_xmlHelper->getXmlFilePath());

//        var_dump($filePath.'/straker_job_37_1470012454.xml');
//
//        exit;

        $parsedArray = $this->_xmlParser->load($filePath.'/straker_job_37_1470012454.xml')->xmlToArray();

        var_dump($parsedArray['root']['data']);
        exit;

        return $parsedArray['xmlNodeName'];


    }

}
