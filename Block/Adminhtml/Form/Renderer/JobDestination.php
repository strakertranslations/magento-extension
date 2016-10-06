<?php

namespace Straker\EasyTranslationPlatform\Block\Adminhtml\Form\Renderer;

use Straker\EasyTranslationPlatform\Api\Data\StrakerAPIInterface;

class JobDestination extends \Magento\Backend\Block\Widget\Form\Renderer\Fieldset\Element implements \Magento\Framework\Data\Form\Element\Renderer\RendererInterface
{
    protected $_template = 'Straker_EasyTranslationPlatform::renderer/form/jobDestination.phtml';

    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        StrakerAPIInterface $strakerAPI,
        array $data = []
    ) {
    
        $this->_strakerAPIinterface = $strakerAPI;
        parent::__construct($context, $data);
    }

    public function render(\Magento\Framework\Data\Form\Element\AbstractElement $element)
    {
        $this->_element = $element;
        $html = $this->toHtml();
        return $html;
    }

    public function getWebsites()
    {

        return $this->_storeManager->getWebsites();
    }

    public function getOptions()
    {

        return $this->_strakerAPIinterface->getLanguages();
    }
}
