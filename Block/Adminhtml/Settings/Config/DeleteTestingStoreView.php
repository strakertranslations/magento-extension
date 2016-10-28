<?php
namespace Straker\EasyTranslationPlatform\Block\Adminhtml\Settings\Config;

use Magento\Backend\Block\Template\Context;
use Magento\Config\Block\System\Config\Form\Field;
use Straker\EasyTranslationPlatform\Api\Data\SetupInterface;

class DeleteTestingStoreView extends Field
{
    const BUTTON_TEMPLATE = 'settings/config/button/delete_test_store_view_button.phtml';

    private $_buttonId;
    private $_buttonName;
    protected $_setup;

    function __construct
    (
        Context $context,
        SetupInterface $setup,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->_setup = $setup;
    }

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

    /**
     * Render button
     *
     * @param  \Magento\Framework\Data\Form\Element\AbstractElement $element
     * @return string
     */
    public function render(\Magento\Framework\Data\Form\Element\AbstractElement $element)
    {
        // Remove scope label
        $element->unsScope()->unsCanUseWebsiteValue()->unsCanUseDefaultValue();
        return parent::render($element);
    }

    /**
     * Return ajax url for button
     *
     * @return string
     */
    public function getAjaxResetUrl()
    {
        return $this->getUrl('EasyTranslationPlatform/Settings/DeleteTestStoreView'); //hit controller by ajax call on button click.
    }

    /**
     * Get the button and scripts contents
     *
     * @param \Magento\Framework\Data\Form\Element\AbstractElement $element
     * @return string
     */
    protected function _getElementHtml(\Magento\Framework\Data\Form\Element\AbstractElement $element)
    {
        $this->_buttonId = $element->getId();
        $this->_buttonName = $element->getName();

        return $this->_toHtml();
    }

    public function getButtonHtml()
    {
        $button = $this->getLayout()->createBlock(
            'Magento\Backend\Block\Widget\Button'
        )->addData([
            'id' => $this->_buttonId,
            'name' => $this->_buttonName,
            'label' => __('Delete'),
            'type' => 'button',
            'disabled' => !$this->_setup->isTestingStoreViewExist()
        ]);

        return $button->toHtml();
    }
}
