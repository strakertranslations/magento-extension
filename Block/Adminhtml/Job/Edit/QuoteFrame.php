<?php

namespace Straker\EasyTranslationPlatform\Block\Adminhtml\Job\Edit;

use Magento\Backend\Block\Widget\Form\Generic;

class QuoteFrame extends Generic
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
