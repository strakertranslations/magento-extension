<?php

namespace Straker\EasyTranslationPlatform\Block\Adminhtml\Job\ViewJob\Grid\Renderer;

use Magento\Backend\Block\Context;
use Magento\Framework\DataObject;
use Magento\Backend\Block\Widget\Grid\Column\Renderer\AbstractRenderer;
use Straker\EasyTranslationPlatform\Model\ResourceModel\AttributeTranslation\CollectionFactory as AttributeTranslationCollection;

class JobAttributeLabel extends AbstractRenderer
{
    protected $_attributeTranslationCollectionFactory;
    /** @var \Straker\EasyTranslationPlatform\Model\ResourceModel\AttributeTranslation\Collection AttributeTranslationCollection  */
    protected $_jobAttributeCollection;

    public function __construct(
        Context $context,
        AttributeTranslationCollection $attributeTranslationCollection
    )
    {
        $this->_attributeTranslationCollectionFactory = $attributeTranslationCollection;
        parent::__construct( $context );
    }

    function render(DataObject $row)
    {
        $isLabel = $row->getData('is_label');
        $jobId = $row->getData('job_id');
        $this->_jobAttributeCollection = $this->_attributeTranslationCollectionFactory->create()
            ->addFieldToFilter('job_id', ['eq' => $jobId] )
            ->addfieldtofilter('is_label', ['eq' => true ]);

        if( strcasecmp($isLabel, 'yes') === 0 ){
            $attrLabel = $row->getData('original_value');
        }else{
            $attrLabel = $this->_getFieldLabel( $row->getData('attribute_id'));
        }


        $row->setData('label', $attrLabel );
        return parent::render($row);
    }

    protected function _getFieldLabel( $attributeId ){
        $data = $this->_jobAttributeCollection->addFieldToFilter( 'attribute_id', [ 'eq' => $attributeId ])->getData();
        return count($data) > 0 ? $data[0]['original_value'] : null;
    }

    protected function _getFrontendLabel(){
        //TODO: Get Frontend Label
        return 'Frontend Label';
    }
}
