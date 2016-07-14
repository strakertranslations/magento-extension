<?php
namespace Straker\EasyTranslationPlatform\Block\Adminhtml\Settings\Config;

use Magento\Config\Block\System\Config\Form\Field;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\Data\Form\Element\AbstractElement;

class RestoreProductData extends Field
{
    const BUTTON_TEMPLATE = 'settings/config/button/restore_product_data_button.phtml';

    private $_buttonId;
    private $_buttonName;

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
        return $this->getUrl('EasyTranslationPlatform/Settings/RestoreProductData'); //hit controller by ajax call on button click.
    }

    /**
     * Get the button and scripts contents
     *
     * @param AbstractElement $element
     * @return string
     */
    protected function _getElementHtml(AbstractElement $element)
    {
        $this->_buttonId = $element->getId();
        $this->_buttonName = $element->getName();

        return $this->_toHtml();

    }

    public function getButtonHtml()
    {
        $objManager = ObjectManager::getInstance();
        $resourceConnection = $objManager->get('Magento\Framework\App\ResourceConnection');

        /** @var \Straker\EasyTranslationPlatform\Helper\Data $dataHelper */
        $dataHelper = $objManager->get('Straker\EasyTranslationPlatform\Helper\Data');

        /** @var \Magento\Framework\DB\Adapter\AdapterInterface $connection */
        $connection = $resourceConnection->getConnection();

        $attributeData = [
            'id' => $this->_buttonId,
            'name' => $this->_buttonName,
            'label' => __('Restore Product Data'),
            'type' => 'button'
        ];

        $validBackupData = true;

        foreach ( $dataHelper->getProductTableArray() as $tableName ){
            $backupTableName = $dataHelper->getBackupTableNames( $tableName );
            if( $connection->isTableExists( $backupTableName ) ){
                $sql = $connection->select()
                    ->from(
                        $backupTableName,
                        array('COUNT(value_id) AS RowCount')
                    );

                $rows = $connection->fetchAll( $sql );

                if( $rows[0]['RowCount'] <= 0){
                    $validBackupData = false;
                    break;
                }
            }else{
                $validBackupData = false;
                break;
            }
        }

        if( !$validBackupData ) {
            $attributeData['disabled'] = 'disabled';
        }

        /** @var \Magento\Framework\View\Element\BlockInterface $button */
        $button = $this->getLayout()->createBlock(
            'Magento\Backend\Block\Widget\Button'
        );

        $button->addData($attributeData);

        return $button->toHtml();
    }

}