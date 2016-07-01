<?php
namespace Straker\EasyTranslationPlatform\Block\Adminhtml;

class Post extends \Magento\Backend\Block\Widget\Grid\Container
{
    protected function _construct()
    {
        $this->_controller = 'adminhtml_post';
        $this->_blockGroup = 'Straker_EasyTranslationPlatform';
        $this->_headerText = __('Manage EasyTranslationPlatform Posts');

        parent::_construct();

        if ($this->_isAllowedAction('Straker_EasyTranslationPlatform::save')) {
            $this->buttonList->update('add', 'label', __('Add New Post'));
        } else {
            $this->buttonList->remove('add');
        }
    }

    protected function _isAllowedAction($resourceId)
    {
        return $this->_authorization->isAllowed($resourceId);
    }
}
