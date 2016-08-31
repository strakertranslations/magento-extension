<?php

namespace Straker\EasyTranslationPlatform\Model\ResourceModel;

use Straker\EasyTranslationPlatform\Helper\BlockHelper;
use Straker\EasyTranslationPlatform\Helper\PageHelper;

class AttributeTranslation extends \Magento\Framework\Model\ResourceModel\Db\AbstractDb
{
    protected $_jobFactory;

    public function __construct(
        \Magento\Framework\Model\ResourceModel\Db\Context $context,
        \Straker\EasyTranslationPlatform\Model\JobFactory $jobFactory,
        $resourcePrefix = null
    ) {
        parent::__construct($context, $resourcePrefix);
        $this->_jobFactory = $jobFactory;
    }

    protected function _construct()
    {
        $this->_init('straker_attribute_translation','attribute_translation_id');
    }

    protected function _beforeSave(\Magento\Framework\Model\AbstractModel $object)
    {
        $job_type = $this->_jobFactory->create()->load($object->getData('job_id'))->getJobTypeId();

        if( $job_type == \Straker\EasyTranslationPlatform\Model\JobType::JOB_TYPE_PAGE )
        {
            if( !is_numeric( $object->getAttributeId()) ){
                $key = array_search( $object->getAttributeId(),array_column( PageHelper::PageAttributes, 'name'));
                $object->setAttributeId($key);
            }
        }

        if( $job_type == \Straker\EasyTranslationPlatform\Model\JobType::JOB_TYPE_BLOCK )
        {
            if( !is_numeric( $object->getAttributeId()) ){
                $key = array_search( $object->getAttributeId(),array_column( BlockHelper::blockAttributes, 'name'));
                $object->setAttributeId($key);
            }
        }

        return parent::_beforeSave($object);
    }
}
