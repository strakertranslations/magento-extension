<?php

namespace Straker\EasyTranslationPlatform\Block\Adminhtml\Job\ViewJob;

use Magento\Backend\Block\Widget\Container;
use Magento\Backend\Block\Widget\Context;
use Straker\EasyTranslationPlatform\Model\JobFactory;
use Straker\EasyTranslationPlatform\Model\JobStatus;
use Straker\EasyTranslationPlatform\Model\JobType;

class Attribute extends Container
{
    protected $_jobFactory;
    /** @var  \Straker\EasyTranslationPlatform\Model\Job */
    protected $_job;
    protected $_referrerId;
    protected $_referrer;
    protected $_jobEntityName;
    protected $_entityId;
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
        $this->_entityId = array_key_exists('entity_id', $this->_requestData) ? $this->_requestData['entity_id'] : 0;
        $this->_referrerId = array_key_exists('job_type_referrer', $this->_requestData) ? $this->_requestData['job_type_referrer'] : 0;
        $this->_getReferrer();
        $this->_getEntityName();

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
            'job_product',
            [
                'label' => __('Back'),
                'onclick' => 'setLocation(\'' .
                    $this->getUrl(
                        'EasyTranslationPlatform/Jobs/ViewJob',
                        [
                            'job_id' => $this->_requestData['job_id'],
                            'job_type_id' => $this->_requestData['job_type_referrer'],
                            'entity_id' => $this->_requestData['entity_id'],
                            'job_key' => $this->_requestData['job_key'],
                            'source_store_id' => $this->_requestData['source_store_id']
                        ]
                    ) . '\') ',
                'class' => 'back',
                'title' => __('Go to ' . $this->_referrer .  ' page')
            ],
            0,
            30
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
                    'label' => __('Manage Jobs'),
                    'url' => $this->getUrl('EasyTranslationPlatform/Jobs/'),
                    'title' => __('Go to Manage Jobs page')
                ],
                [
                    'label' => empty($this->_job->getJobNumber()) ? __('Sub-job') : $this->_job->getJobNumber(),
                    'url' => $this->getUrl('EasyTranslationPlatform/Jobs/ViewJob',
                        [
                            'job_id' => $this->_requestData['job_id'],
                            'job_key'=> $this->_requestData['job_key'],
                            'job_type_id' => 0,
                            'source_store_id' => $this->_requestData['source_store_id']
                        ]),
                    'title' => __('View content type details')
                ],
                [
                    'label' => __($this->_referrer),
                    'url' => $this->getUrl('EasyTranslationPlatform/Jobs/ViewJob',
                        [
                            'job_id' => $this->_requestData['job_id'],
                            'job_type_id' => $this->_requestData['job_type_referrer'],
//                            'entity_id' => $this->_requestData['entity_id'],
                            'job_key' => $this->_requestData['job_key'],
                            'source_store_id' => $this->_requestData['source_store_id']
                        ]),
                    'title' => __('Go to ' . $this->_referrer . ' page')

                ],
                [
                    'label' => __($this->_jobEntityName)
                ]
            ]
        );

        $this->addChild(
            'straker_job_attribute_grid',
            'Straker\EasyTranslationPlatform\Block\Adminhtml\Job\ViewJob\Attribute\Grid',
            ['id' => 'straker_job_attribute_grid']
        );

        return parent::_prepareLayout();
    }

    protected function _getReferrer(){
        switch ($this->_referrerId){
            case JobType::JOB_TYPE_PRODUCT:
                $this->_referrer = 'Product List';
                break;
            case JobType::JOB_TYPE_CATEGORY:
                $this->_referrer = 'Category List';
                break;
            case JobType::JOB_TYPE_PAGE:
                $this->_referrer = 'Page List';
                break;
            case JobType::JOB_TYPE_BLOCK:
                $this->_referrer = 'Block List';
                break;
        }

    }

    protected function _getEntityName(){
        $this->_jobEntityName = $this->_job->getEntityName( $this->_entityId );
    }

    function _toHtml()
    {
        return $this->getChildHtml('straker-breadcrumbs') . $this->getChildHtml('straker_job_attribute_grid');
    }
}
