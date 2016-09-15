<?php

namespace Straker\EasyTranslationPlatform\Block\Adminhtml\Job\ViewJob;

use Magento\Backend\Block\Widget\Container;
use Magento\Backend\Block\Widget\Context;
use Straker\EasyTranslationPlatform\Model\JobFactory;
use Straker\EasyTranslationPlatform\Model\JobStatus;

class Product extends Container
{
    protected $_jobFactory;
    /** @var  \Straker\EasyTranslationPlatform\Model\Job */
    protected $_job;
    protected $_requestData;

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
        $this->_requestData = $this->getRequest()->getParams();
        $this->_job = $this->_jobFactory->create()->load($this->_requestData['job_id']);

        if($this->_job->getJobStatusId() == JobStatus::JOB_STATUS_COMPLETED){
            $this->addButton(
                'confirm',
                [
                    'label' => __('Confirm'),
                    'onclick' => 'setLocation(\'' . $this->getUrl('EasyTranslationPlatform/Jobs/Confirm', [
                            'job_id' => $this->_job->getId(),
                            'job_key' => $this->_job->getJobKey(),
                            'job_type_id' => $this->_job->getJobTypeId()
                        ] ) . '\') ',
                    'class' => 'primary',
                    'title' => __( 'Confirm the job of ' . $this->_job->getJobNumber() )
                ],
                0,
                50
            );
        }

//        $this->addButton(
//            'confirm',
//            [
//                'label' => __('Confirm'),
//                'onclick' => 'setLocation(\'' . $this->getUrl('EasyTranslationPlatform/Jobs/Confirm', [
//                        'job_id' => $this->_job->getId(),
//                        'job_key' => $this->_job->getJobKey(),
//                        'job_type_id' => $this->_job->getJobTypeId()
//                    ] ) . '\') ',
//                'class' => 'primary',
//                'disabled' => ($this->_job->getJobStatusId() == JobStatus::JOB_STATUS_COMPLETED) ? '':'disabled'
//            ],
//            0,
//            50
//        );

        $this->addButton(
            'job_type',
            [
                'label' => __('Back'),
                'onclick' => 'setLocation(\''
                    . $this->getUrl('EasyTranslationPlatform/Jobs/ViewJob',
                        [
                            'job_id' => $this->_requestData['job_id'],
                            'job_key'=> $this->_requestData['job_key'],
                            'job_type_id' => 0,
                            'source_store_id' => $this->_requestData['source_store_id']
                        ]) . '\') ',
                'class' => 'back',
                'title' => __('Go to Job Types page')
            ],
            0,
            20
        );

        parent::_construct();
    }

    protected function _prepareLayout()
    {
        $this->addChild(
            'straker-breadcrumbs',
            'Straker\EasyTranslationPlatform\Block\Adminhtml\Job\ViewJob\Widget\Breadcrumbs',
            [
                [
                    'label' => 'Manage Jobs',
                    'url' => $this->getUrl('EasyTranslationPlatform/Jobs/'),
                    'title' => 'Go to Manage Jobs page'
                ],
                [
                    'label' => empty($this->_job->getJobNumber()) ? 'Sub-job' : $this->_job->getJobNumber(),
                    'url' => $this->getUrl('EasyTranslationPlatform/Jobs/ViewJob',
                        [
                            'job_id' => $this->_requestData['job_id'],
                            'job_key'=> $this->_requestData['job_key'],
                            'job_type_id' => 0,
                            'source_store_id' => $this->_requestData['source_store_id']
                        ]),
                    'title' => 'Go to Job Types page'
                ],
                [
                    'label' => 'Product List',
                ]
            ]
        );

        $this->addChild(
            'straker_job_product_grid',
            'Straker\EasyTranslationPlatform\Block\Adminhtml\Job\ViewJob\Product\Grid'
        );

        return parent::_prepareLayout();
    }

    function _toHtml()
    {
        return $this->getChildHtml('straker-breadcrumbs') . $this->getChildHtml('straker_job_product_grid');
    }

}
