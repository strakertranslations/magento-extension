<?php

namespace Straker\EasyTranslationPlatform\Block\Adminhtml\Job;

class QuoteFrame extends \Magento\Backend\Block\Widget\Form\Generic
{
    const BUTTON_TEMPLATE = 'job/quote-frame.phtml';

    /**
     * Set template to itself
     *
     * @return $this
     */
    protected function _prepareLayout()
    {
        parent::_prepareLayout();
        if (!$this->getTemplate()) {
            $this->setTemplate(static::BUTTON_TEMPLATE);
        }
        return $this;
    }
}
