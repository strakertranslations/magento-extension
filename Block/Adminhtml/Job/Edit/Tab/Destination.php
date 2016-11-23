<?php

namespace Straker\EasyTranslationPlatform\Block\Adminhtml\Job\Edit\Tab;

use Straker\EasyTranslationPlatform\Api\Data\StrakerAPIInterface;

use Magento\Backend\Block\Template\Context;
use Magento\Framework\Registry;
use Magento\Framework\Data\FormFactory;
use Magento\Store\Model\System\Store;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Backend\Model\Session;

class Destination extends \Magento\Backend\Block\Widget\Form\Generic implements \Magento\Backend\Block\Widget\Tab\TabInterface
{

    public function __construct(
        Context $context,
        Registry $registry,
        FormFactory $formFactory,
        StoreManagerInterface $storeManager,
        StrakerAPIInterface $strakerAPIInterface,
        Session $session
    ) {

        $this->_storeManager = $storeManager;
        $this->_strakerAPIinterface = $strakerAPIInterface;
        $this->_formFactory = $formFactory;
        $this->_Registry = $registry;
        $this->session = $session;

        parent::__construct($context, $registry, $formFactory);
    }


    protected function _construct()
    {
        parent::_construct();
    }

    protected function _prepareForm()
    {

        $form = $this->_formFactory->create();

        $form->setHtmlIdPrefix('job_');

        $fieldset = $form->addFieldset(
            '',
            []
        );

        $renderer = $this->getLayout()->createBlock(
            'Straker\EasyTranslationPlatform\Block\Adminhtml\Form\Renderer\JobDestination'
        );

        $fieldset->setRenderer($renderer);

        $this->setForm($form);

        return parent::_prepareForm();
    }

    public function getWebsites()
    {

        return $this->_storeManager->getWebsites();
    }


    /**
     * Prepare label for tab
     *
     * @return \Magento\Framework\Phrase
     */
    public function getTabLabel()
    {
        return __('1. &nbsp; Select Destination');
    }

    /**
     * Prepare title for tab
     *
     * @return \Magento\Framework\Phrase
     */
    public function getTabTitle()
    {
        return __('Select Destination');
    }

    /**
     * {@inheritdoc}
     */
    public function canShowTab()
    {
        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function isHidden()
    {
        return false;
    }
}
