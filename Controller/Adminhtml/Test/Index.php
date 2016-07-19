<?php

namespace Straker\EasyTranslationPlatform\Controller\Adminhtml\Test;

use Magento\Backend\App\Action;
use Magento\Framework\View\Result\PageFactory;
use Magento\Framework\Controller\Result\JsonFactory;
use Magento\Eav\Api\AttributeRepositoryInterface;
use Magento\Eav\Model\ResourceModel\Entity\Attribute\Collection;
use Magento\Framework\Locale\ListsInterface;

class Index extends \Magento\Backend\App\Action
{

    protected $resultPageFactory;

    public function __construct(
        Action\Context $context,
        PageFactory $pageFactory,
        JsonFactory $jsonFactory,
        Collection $attCollection,
        ListsInterface $list
    )
    {
        $this->attributeCollection = $attCollection;
        $this->resultPageFactory = $pageFactory;
        $this->jsonFactory = $jsonFactory;
        $this->localList = $list;
        return parent::__construct($context);
    }

    public function execute()
    {
        $resultLayout = $this->resultPageFactory->create();
        return $resultLayout;
    }

}
