<?php
/**
 * Created by PhpStorm.
 * User: Paul
 * Date: 5/07/16
 * Time: 15:31
 */

namespace Straker\EasyTranslationPlatform\Block\Adminhtml\Settings\Config;


class FiledDisabled extends \Magento\Config\Block\System\Config\Form\Field
{
    protected function _getElementHtml(\Magento\Framework\Data\Form\Element\AbstractElement $element)
    {
        $element->setDisabled('disabled');
        $this->setData(
            [
                'value' => '2342'
            ]
        );
        return $element->getElementHtml();

    }
}