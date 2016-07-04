<?php
namespace Straker\EasyTranslationPlatform\Block\Adminhtml\Job;

class Edit extends \Magento\Backend\Block\Widget\Form\Container
{
    /**
     * Core registry
     *
     * @var \Magento\Framework\Registry
     */
    protected $_coreRegistry = null;

    /**
     * @param \Magento\Backend\Block\Widget\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param array $data
     */
    public function __construct(
        \Magento\Backend\Block\Widget\Context $context,
        \Magento\Framework\Registry $registry,
        array $data = []
    ) {
        $this->_coreRegistry = $registry;
        parent::__construct($context, $data);
    }

    /**
     * Initialize easytranslationplatform job edit block
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_objectId = 'job_id';
        $this->_blockGroup = 'Straker_EasyTranslationPlatform';
        $this->_controller = 'adminhtml_job';

        parent::_construct();

        if ($this->_isAllowedAction('Straker_EasyTranslationPlatform::save')) {
            $this->buttonList->update('save', 'label', __('Save EasyTranslationPlatform Job'));
            $this->buttonList->add(
                'saveandcontinue',
                [
                    'label' => __('Save and Continue Edit'),
                    'class' => 'save',
                    'data_attribute' => [
                        'mage-init' => [
                            'button' => ['event' => 'saveAndContinueEdit', 'target' => '#edit_form'],
                        ],
                    ]
                ],
                -100
            );
        } else {
            $this->buttonList->remove('save');
        }

        if ($this->_isAllowedAction('Straker_EasyTranslationPlatform::job_delete')) {
            $this->buttonList->update('delete', 'label', __('Delete Job'));
        } else {
            $this->buttonList->remove('delete');
        }
    }

    /**
     * Retrieve text for header element depending on loaded job
     *
     * @return \Magento\Framework\Phrase
     */
    public function getHeaderText()
    {
        if ($this->_coreRegistry->registry('easytranslationplatform_job')->getId()) {
            return __("Edit Job '%1'", $this->escapeHtml($this->_coreRegistry->registry('easytranslationplatform_job')->getTitle()));
        } else {
            return __('New Job');
        }
    }

    /**
     * Check permission for passed action
     *
     * @param string $resourceId
     * @return bool
     */
    protected function _isAllowedAction($resourceId)
    {
        return $this->_authorization->isAllowed($resourceId);
    }

    /**
     * Getter of url for "Save and Continue" button
     * tab_id will be replaced by desired by JS later
     *
     * @return string
     */
    protected function _getSaveAndContinueUrl()
    {
        return $this->getUrl('easytranslationplatform/*/save', ['_current' => true, 'back' => 'edit', 'active_tab' => '']);
    }
}
