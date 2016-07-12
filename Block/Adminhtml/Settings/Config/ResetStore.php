<?php
namespace Straker\EasyTranslationPlatform\Block\Adminhtml\Settings\Config;

use Magento\Backend\Block\System\Store\Store;
use Magento\Framework\App\Config\ScopeConfigInterface;

class ResetStore extends \Magento\Config\Block\System\Config\Form\Field
{
    const BUTTON_TEMPLATE = 'settings/config/button/reset_store_button.phtml';

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
        return $this->getUrl('EasyTranslationPlatform/Settings/ResetStore'); //hit controller by ajax call on button click.
    }

    /**
     * Get the button and scripts contents
     *
     * @param \Magento\Framework\Data\Form\Element\AbstractElement $element
     * @return string
     */
    protected function _getElementHtml(\Magento\Framework\Data\Form\Element\AbstractElement $element)
    {
        return $this->_toHtml();
    }

    public function getWebsites() {
        return $this->_storeManager->getWebsites();
    }

    public function getConfig( $storeId ){
        $this->_scopeConfig->getValue('straker/general/source_store', 'stores', $storeId );
    }

    public function getClearStoreButtonHtml( $store )
    {
        $objManager = \Magento\Framework\App\ObjectManager::getInstance();
        $configHelper = $objManager->get('Straker\EasyTranslationPlatform\Helper\ConfigHelper');

        if ($store->getId() && $configHelper->getStoreSetup($store->getId())) {
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