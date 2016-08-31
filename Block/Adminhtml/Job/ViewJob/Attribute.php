<?php

namespace Straker\EasyTranslationPlatform\Block\Adminhtml\Job\ViewJob;

use Magento\Backend\Block\Widget\Container;
use Magento\Backend\Block\Widget\Context;
use Straker\EasyTranslationPlatform\Model\JobFactory;
use Straker\EasyTranslationPlatform\Model\JobStatus;

class Attribute extends Container
{
    protected $_jobFactory;

    public function __construct(
        Context $context,
        JobFactory $jobFactory,
        array $data = []
    )
    {
        $this->_jobFactory = $jobFactory;
        parent::__construct($context, $data);
    }

    public function _construct()
    {
        $requestData = $this->getRequest()->getParams();
        $job = $this->_jobFactory->create()->load($requestData['job_id']);
        if ( $job->getJobStatusId() == JobStatus::JOB_STATUS_COMPLETED) {
            $this->addButton(
                'confirm',
                [
                    'label' => __('Confirm'),
                    'onclick' => 'setLocation(\'' . $this->getUrl('EasyTranslationPlatform/Jobs/Confirm', [
                            'job_id' => $job->getId(),
                            'job_key' => $job->getJobKey(),
                            'job_type_id' => $job->getJobTypeId()
                        ] ) . '\') ',
                    'class' => 'primary'
                ],
                -2
            );
        }

        $this->addButton(
            'back',
            [
                'label' => __('Back'),
                'onclick' => 'setLocation(\'' .
                    $this->getUrl(
                        'EasyTranslationPlatform/Jobs/ViewJob',
                        [
                            'job_id' => $requestData['job_id'],
                            'job_type_id' => $requestData['job_type_referrer'],
                            'entity_id' => $requestData['entity_id'],
                            'job_key' => $requestData['job_key'],
                            'source_store_id' => $requestData['source_store_id']
                        ]
                    ) . '\') ',
                'class' => 'back'
            ],
            -1
        );

        parent::_construct();
    }

    protected function _prepareLayout()
    {
        $this->addChild(
            'straker_job_attribute_grid',
            'Straker\EasyTranslationPlatform\Block\Adminhtml\Job\ViewJob\Attribute\Grid',
            ['id' => 'straker_job_attribute_grid']
        );

        return parent::_prepareLayout();
    }

    function _toHtml()
    {
        return $this->getChildHtml('straker_job_attribute_grid');
    }
}
