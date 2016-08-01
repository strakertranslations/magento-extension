<?php

namespace Straker\EasyTranslationPlatform\Helper;

use Magento\Framework\App\Helper\Context;
use Magento\Eav\Model\AttributeRepository;

use Straker\EasyTranslationPlatform\Logger\Logger;

class AttributeHelper extends \Magento\Framework\App\Helper\AbstractHelper
{

    protected $_translatedAttributeLabels = [];

    public function __construct(

        Context $context,
        AttributeRepository $attributeRepository,
        Logger $logger

    ) {

        $this->_attributeRepository = $attributeRepository;
        $this->_logger = $logger;
        parent::__construct($context);
    }

    public function getConfigurableAttributes($product)
    {

        $attributes = $product->getTypeInstance(true)->getConfigurableAttributesAsArray($product);

        $configAttributeData = [];

        foreach ($attributes as $attribute)
        {
            $value_data = [];

            foreach ($attribute['values'] as $value){

                $value_data[] = ['option_id'=>$value['value_index'],'value'=>$value['default_label']];
            }

            $configAttributeData[] = [
                'attribute_id'=>$attribute['attribute_id'],
                'label'=>$attribute['label'],
                'value'=>$value_data
            ];

        }

        return $configAttributeData;

    }

    public function findMultiOptionAttributes($attribute_id, $product, $store_id)
    {

        $attribute = $this->_attributeRepository->get(\Magento\Catalog\Model\Product::ENTITY,$attribute_id);

        $options = $product->getResource()->getAttributeRawValue($product->getId(), $attribute, $store_id);

        if($options){

            $values['attribute_id'] = $attribute_id;

            $values['label'] = $attribute->getFrontendLabel();

            $options = explode(',',$options);

            foreach ($options as $option_id)
            {
                $values['value'][] = ['option_id'=>$option_id,'value'=>$attribute->getSource()->getOptionText($option_id)];
            }

            return $values;

        }

        return false;

    }

    public function appendAttributeLabel(
        $productData,
        $attribute,
        $jobName,
        $source_store_id,
        $xmlHelper
    )
    {

        if($productData){

            try{

                $xmlHelper->appendDataToRoot([
                    'name' => $jobName,
                    'content_context' => 'product_attribute_label',
                    'content_context_url' => $productData['product_url'],
                    'translation_id'=>$attribute['label_translation_id'],
                    'source_store_id'=> $source_store_id,
                    'product_id' => $productData['product_id'],
                    'attribute_id'=>$attribute['attribute_id'],
                    'attribute_label'=>$attribute['label'],
                    'value'=>$attribute['label'],
                    'translate' => in_array($attribute['label'],$this->_translatedAttributeLabels) ? 'false' : 'true'
                ]);

                array_push($this->_translatedAttributeLabels,$attribute['label']);

            }catch (\Exception $e){

                $this->_logger->error('error',__FILE__.' '.__LINE__.''.$e->getMessage(),$e);

            }
        }
    }


}
