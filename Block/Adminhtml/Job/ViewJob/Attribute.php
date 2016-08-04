<?php

namespace Straker\EasyTranslationPlatform\Block\Adminhtml\Job\ViewJob;

use Magento\Backend\Block\Widget\Container;

class Attribute extends Container
{
    protected $_jobId;
    protected $_entityId;

    public function _construct()
    {
        $requestData = $this->getRequest()->getParams();

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
                            'entity_id' => $requestData['entity_id']
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
            'Straker\EasyTranslationPlatform\Block\Adminhtml\Job\ViewJob\Attribute\Grid'
        );

        return parent::_prepareLayout();
    }

    function _toHtml()
    {
        return $this->getChildHtml('straker_job_attribute_grid');
    }
}
