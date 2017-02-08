<?php
namespace Straker\EasyTranslationPlatform\Block\Adminhtml\Job;

use Magento\Backend\Block\Widget\Container;
use Magento\Backend\Block\Widget\Context;
use Magento\Framework\Registry;

class ViewQuote extends Container
{
    protected $_coreRegistry;
    const QUOTE_TEMPLATE = 'job/quote-frame.phtml';

    function __construct(
        Context $context,
        Registry $registry,
        array $data
    ) {
        $this->_coreRegistry = $registry;
        parent::__construct($context, $data);
    }

    function _construct()
    {
        parent::_construct();

        $this->addButton(
            'back',
            [
                'label' => __('Back'),
                'onclick' => 'setLocation(\'' . $this->getUrl('EasyTranslationPlatform/Jobs/Index') . '\') ',
                'class' => 'back',
                'title' => __('Go to Manage Jobs page')
            ]
        );

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
