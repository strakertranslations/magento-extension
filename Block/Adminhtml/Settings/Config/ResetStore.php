<?php
namespace Straker\EasyTranslationPlatform\Block\Adminhtml\Settings\Config;

use Magento\Backend\Block\System\Store\Store;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Backend\Block\Widget\Context;
use Magento\Framework\Data\Form\Element\AbstractElement;
use Straker\EasyTranslationPlatform\Helper\ConfigHelper;

class ResetStore extends \Magento\Config\Block\System\Config\Form\Field
{
    const BUTTON_TEMPLATE = 'settings/config/button/reset_store_button.phtml';
    protected $_configHelper;

    public function __construct(
        Context $context,
        ConfigHelper $configHelper,
        array $data = []
    ) {
        $this->_configHelper = $configHelper;
        parent::__construct($context, $data);
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
     * @param  AbstractElement $element
     * @return string
     */
    public function render(AbstractElement $element)
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
        return $this->getUrl('EasyTranslationPlatform/Settings/ResetStore'); //hit controller by ajax call on button click.
    }

    /**
     * Get the button and scripts contents
     *
     * @param AbstractElement $element
     * @return string
     */
    protected function _getElementHtml(AbstractElement $element)
    {
        return $this->_toHtml();
    }

    /**
     * @return \Magento\Store\Api\Data\WebsiteInterface[]
     */
    public function getWebsites() {
        return $this->_storeManager->getWebsites();
    }

    /**
     * @param $store
     * @return string
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getClearStoreButtonHtml( $store )
    {
        if ($store->getId() && $this->_configHelper->getStoreSetup($store->getId())) {
            $button = $this->getLayout()->createBlock('Magento\Backend\Block\Widget\Button')
                ->setData([
                    'id' => 'straker_reset_store_button_' . $store->getCode(),
                    'label' => __('Clear'),
                    'class' => 'straker-reset-store-button'
                ]);
            return $button->toHtml();
        }
        else{

            return '<div class="empty-button">'.__('No language settings applied').'</div>';
        }
    }

}