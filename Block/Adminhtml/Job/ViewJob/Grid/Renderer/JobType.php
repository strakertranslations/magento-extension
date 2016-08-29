<?php

namespace Straker\EasyTranslationPlatform\Block\Adminhtml\Job\ViewJob\Grid\Renderer;

use Magento\Framework\DataObject;
use Magento\Backend\Block\Widget\Grid\Column\Renderer\AbstractRenderer;
use Straker\EasyTranslationPlatform\Model\JobType as JobTypeModel;

class JobType extends AbstractRenderer
{

    function render(DataObject $row)
    {
        $jobTypeId = $row->getData('job_type_id');
        $row->setData('job_type', ucwords(JobTypeModel::JOBTYPE[$jobTypeId-1]));
        return parent::render($row);
    }
}
