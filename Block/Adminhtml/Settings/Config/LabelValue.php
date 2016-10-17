<?php
/**
 * Created by PhpStorm.
 * User: Paul
 * Date: 5/07/16
 * Time: 15:31
 */

namespace Straker\EasyTranslationPlatform\Block\Adminhtml\Settings\Config;

class LabelValue extends \Magento\Config\Block\System\Config\Form\Field
{
    function _getElementHtml(\Magento\Framework\Data\Form\Element\AbstractElement $element)
    {
        return '<div class="straker-value">' . $element->getEscapedValue() . '</div>';
    }
}
