<?php

namespace Straker\EasyTranslationPlatform\Block\Adminhtml\Form\Renderer;

use Magento\Backend\Block\Template\Context;
use Magento\Backend\Block\Widget\Form\Renderer\Fieldset\Element;
use Magento\Framework\Data\Form\Element\AbstractElement;
use Magento\Framework\Data\Form\Element\Renderer\RendererInterface;
use Magento\Framework\Exception\NoSuchEntityException;
use Straker\EasyTranslationPlatform\Api\Data\StrakerAPIInterface;
use Straker\EasyTranslationPlatform\Helper\ConfigHelper;
use Straker\EasyTranslationPlatform\Helper\Data;

class JobDestination extends Element implements RendererInterface
{
    protected $_template = 'Straker_EasyTranslationPlatform::renderer/form/jobDestination.phtml';

    /**
     * @var \Straker\EasyTranslationPlatform\Api\Data\StrakerAPIInterface
     */
    protected $_strakerAPIInterface;
    protected $_configHelper;
    protected $_dataHelper;

    public function __construct(
        Context $context,
        StrakerAPIInterface $strakerAPI,
        ConfigHelper $configHelper,
        Data $dataHelper,
        array $data = []
    ) {
    
        $this->_strakerAPIInterface = $strakerAPI;
        $this->_configHelper = $configHelper;
        $this->_dataHelper = $dataHelper;
        parent::__construct($context, $data);
    }

    public function render(AbstractElement $element)
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
        if($this->isSandboxModeEnabled()){
            return $this->getSandboxLanguages();
        }
        return $this->_strakerAPIInterface->getLanguages();
    }

    public function isSandboxModeEnabled(){
//        if($this->_configHelper->isSandboxMode()){
//            try{
//                $this->_storeManager->getStore($this->_configHelper->getTestingStoreViewCode());
//                return true;
//            }catch (NoSuchEntityException $e){
//                return false;
//            }
//        }else{
//            return false;
//        }
        return $this->_configHelper->isSandboxMode();
    }

    public function getSandboxLanguages(){
        return $this->_dataHelper->getSandboxLanguages();
    }

    public function getTestingStoreCode(){
        return $this->_configHelper->getTestingStoreViewCode();
    }
}
