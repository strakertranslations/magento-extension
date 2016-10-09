<?php
namespace Straker\EasyTranslationPlatform\Block\Adminhtml\Job;

use Magento\Framework\View\Element\Template;
use Magento\Framework\Registry;

class ViewQuote extends Template
{
    protected $_coreRegistry;
    const QUOTE_TEMPLATE = 'job/quote-frame.phtml';

    function __construct(
        Template\Context $context,
        Registry $registry,
        array $data
    ) {
    
        $this->_coreRegistry = $registry;
        parent::__construct($context, $data);
    }

    protected function _prepareLayout()
    {
        parent::_prepareLayout();
        if (!$this->getTemplate()) {
            $this->setTemplate(static::QUOTE_TEMPLATE);
        }
        return $this;
    }

    public function getQuoteFrameHtml(){
        $quoteUrl = $this->_coreRegistry->registry('quote_url');
        $this->_coreRegistry->unregister('quote_url');
        return $quoteUrl;
    }
}
