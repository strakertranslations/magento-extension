<?php

namespace Straker\EasyTranslationPlatform\Block\Adminhtml\Form\Renderer;

class Sourcefield extends \Magento\Backend\Block\Widget\Form\Renderer\Fieldset\Element implements \Magento\Framework\Data\Form\Element\Renderer\RendererInterface
{
    protected $_template = 'Straker_EasyTranslationPlatform::renderer/form/sourcefield.phtml';

    public function render(\Magento\Framework\Data\Form\Element\AbstractElement $element)
    {
        $this->_element = $element;
        $html = $this->toHtml();
        return $html;
    }

    public function getWebsites() {

        return $this->_storeManager->getWebsites();
    }
}