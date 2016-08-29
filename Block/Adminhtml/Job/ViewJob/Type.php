<?php

namespace Straker\EasyTranslationPlatform\Block\Adminhtml\Job\ViewJob;

use Magento\Backend\Block\Widget\Container;
use Magento\Framework\View\Element\Template;
use Straker\EasyTranslationPlatform\Model;


class Type extends Container
{
    /** @var \Straker\EasyTranslationPlatform\Model\Job $_job */
    protected $_job;
    protected $_entityId;
    protected $_jobTypeId = Model\JobType::JOB_TYPE_ATTRIBUTE;

    public function _construct()
    {
        $this->addButton(
            'back',
            [
                'label' => __('Back'),
                'onclick' => 'setLocation(\'' . $this->getUrl('EasyTranslationPlatform/Jobs/') . '\') ',
                'class' => 'back'
            ],
            -1
        );

        parent::_construct();
    }

    protected function _prepareLayout()
    {
        $this->addChild(
            'straker_job_type_grid',
            'Straker\EasyTranslationPlatform\Block\Adminhtml\Job\ViewJob\Type\Grid'
        );

        return parent::_prepareLayout();
    }

    function _toHtml()
    {
        return $this->getChildHtml('straker_job_type_grid');
    }

}
