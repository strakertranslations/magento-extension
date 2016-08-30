<?php

namespace Straker\EasyTranslationPlatform\Block\Adminhtml\Job\ViewJob;

use Magento\Backend\Block\Widget\Container;
use Magento\Framework\View\Element\Template;
use Straker\EasyTranslationPlatform\Model;


class Page extends Container
{
    /** @var \Straker\EasyTranslationPlatform\Model\Job $_job */
    protected $_job;
    protected $_entityId;
    protected $_jobTypeId = Model\JobType::JOB_TYPE_ATTRIBUTE;

    public function _construct()
    {
        $requestData = $this->getRequest()->getParams();
        $this->addButton(
            'back',
            [
                'label' => __('Back'),
                'onclick' => 'setLocation(\''
                    . $this->getUrl('EasyTranslationPlatform/Jobs/ViewJob',
                        [
                            'job_key'=> $requestData['job_key'],
                            'job_type_id' => 0,
                            'source_store_id' => $requestData['source_store_id']
                        ])  . '\') ',
                'class' => 'back'
            ],
            -1
        );

        parent::_construct();
    }

    protected function _prepareLayout()
    {
        $this->addChild(
            'straker_job_page_grid',
            'Straker\EasyTranslationPlatform\Block\Adminhtml\Job\ViewJob\Page\Grid'
        );

        return parent::_prepareLayout();
    }

    function _toHtml()
    {
        return $this->getChildHtml('straker_job_page_grid');
    }

}
