<?php
/**
 * Created by PhpStorm.
 * User: Paul
 * Date: 31/10/16
 * Time: 09:24
 */

namespace Straker\EasyTranslationPlatform\Block\Adminhtml\Job\ViewJob\Grid\Renderer;


use Magento\Backend\Block\Widget\Grid\Column\Renderer\Action;

class MultiAction extends Action
{
    /**
     * Renders column
     *
     * @param  \Magento\Framework\DataObject $row
     * @return string
     */
    public function render(\Magento\Framework\DataObject $row)
    {
        $html = '';
        $actions = $this->getColumn()->getActions();
        if (!empty($actions) && is_array($actions)) {
            $links = [];
            foreach ($actions as $action) {
                if (is_array($action)) {
                    $link = $this->_toLinkHtml($action, $row);
                    if ($link) {
                        $links[] = $link;
                    }
                }
            }
            $html = implode('<br />', $links);
        }

        if ($html == '') {
            $html = '&nbsp;';
        }

        return $html;
    }
}