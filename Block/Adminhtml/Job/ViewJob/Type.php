<?php

namespace Straker\EasyTranslationPlatform\Block\Adminhtml\Job\ViewJob;

use Magento\Backend\Block\Widget\Container;
use Magento\Backend\Block\Widget\Context;
use Straker\EasyTranslationPlatform\Helper\ConfigHelper;
use Straker\EasyTranslationPlatform\Model\JobFactory;
use Straker\EasyTranslationPlatform\Model\JobStatus;

class Type extends Container
{
    protected $_jobFactory;
    /** @var  \Straker\EasyTranslationPlatform\Model\Job */
    protected $_job;
    protected $_requestData;
    protected $_configHelper;

    public function __construct(
        Context $context,
        JobFactory $jobFactory,
        ConfigHelper $configHelper,
        array $data = []
    ) {
    
        $this->_jobFactory = $jobFactory;
        $this->_configHelper = $configHelper;
        parent::__construct($context, $data);
    }

    public function _construct()
    {
        $this->_requestData = $this->getRequest()->getParams();
        $this->_job = $this->_jobFactory->create()->load($this->_requestData['job_id']);
        $file = $this->_configHelper->getStrakerPath().'/jobs/original/straker_job_1&2_1497831610.xml';
        if ($this->_job->getJobStatusId() >= JobStatus::JOB_STATUS_INIT) {
            $this->addButton(
                'export_source_file',
                [
                    'label' => __('Export'),
                    'onclick' => 'setLocation(\'' . $this->getUrl('EasyTranslationPlatform/Jobs/Export', [
                            'job_id'            => $this->_job->getId(),
                            'job_key'           => $this->_job->getJobKey(),
                            'source_store_id'   => $this->_job->getSourceStoreId()
                        ]) . '\') ',
                    'class' => 'primary',
                    'title' => __('Export to XML file.')
                ],
                0,
                50
            );
        }

        if ($this->_job->getJobStatusId() == JobStatus::JOB_STATUS_COMPLETED) {
            $this->addButton(
                'publish',
                [
                    'label' => __('Publish'),
                    'onclick' => 'setLocation(\'' . $this->getUrl('EasyTranslationPlatform/Jobs/Confirm', [
                            'job_id' => $this->_job->getId(),
                            'job_key' => $this->_job->getJobKey(),
                            'job_type_id' => $this->_job->getJobTypeId()
                        ]) . '\') ',
                    'class' => 'primary',
                    'title' => __('Publish the job of ' . $this->_job->getJobNumber())
                ],
                0,
                50
            );
        }

        $this->addButton(
            'manage_job',
            [
                'label' => __('Back'),
                'onclick' => 'setLocation(\'' . $this->getUrl('EasyTranslationPlatform/Jobs/') . '\') ',
                'class' => 'back',
                'title' => __('Go to Manage Jobs page')
            ],
            0,
            30
        );

        parent::_construct();
    }

    protected function _prepareLayout()
    {

        $this->addChild(
            'straker-title-manageJob',
            'Magento\Framework\View\Element\Template'
        )->setTemplate('Straker_EasyTranslationPlatform::job/viewJobTitle.phtml')->setData('title','Manage Jobs');

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
                    'label' => empty($this->_job->getJobNumber()) ? __('Sub-job') : $this->_job->getJobNumber()
                ]
            ]
        );

        $this->addChild(
            'straker_job_type_grid',
            'Straker\EasyTranslationPlatform\Block\Adminhtml\Job\ViewJob\Type\Grid'
        );

        return parent::_prepareLayout();
    }

    public function _toHtml()
    {
        return $this->getChildHtml();
    }
}
