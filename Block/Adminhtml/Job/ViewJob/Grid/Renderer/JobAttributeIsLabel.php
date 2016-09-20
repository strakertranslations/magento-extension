<?php

namespace Straker\EasyTranslationPlatform\Block\Adminhtml\Job\ViewJob\Grid\Renderer;

use Magento\Backend\Block\Context;
use Magento\Catalog\Api\Data\ProductAttributeInterface;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Eav\Api\AttributeRepositoryInterface;
use Magento\Eav\Model\AttributeFactory;
use Magento\Catalog\Model\ProductFactory;
use Magento\Eav\Model\Entity\StoreFactory;
use Magento\Framework\DataObject;
use Magento\Backend\Block\Widget\Grid\Column\Renderer\AbstractRenderer;
use Straker\EasyTranslationPlatform\Api\JobRepositoryInterface;
use Straker\EasyTranslationPlatform\Model\JobFactory;
use Straker\EasyTranslationPlatform\Model\ResourceModel\AttributeTranslation\CollectionFactory as AttributeTranslationCollection;

class JobAttributeIsLabel extends AbstractRenderer
{
    function render(DataObject $row)
    {
        $row->setData('is_label', $row->getData('is_label') ? __('Yes') : __('No') );
        return parent::render($row);
    }
}
