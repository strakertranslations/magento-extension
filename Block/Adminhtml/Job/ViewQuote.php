<?php
namespace Straker\EasyTranslationPlatform\Block\Adminhtml\Job;

use Magento\Framework\View\Element\Template;
use Magento\Framework\Registry;

class ViewQuote extends Template
{
    protected $_coreRegistry;
    
    function __construct(
        Template\Context $context,
        Registry $registry,
        array $data
    ) {
    
        $this->_coreRegistry = $registry;
        parent::__construct($context, $data);
    }

//    public function getQuoteFrameHtml(){
//        var_dump( $this->_coreRegistry->registry('quote_url') );
//        exit();
//
//    }
}
