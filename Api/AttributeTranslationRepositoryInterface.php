<?php
namespace Straker\EasyTranslationPlatform\Api;

use Straker\EasyTranslationPlatform\Model\AttributeTranslationInterface;
use Magento\Framework\Api\SearchCriteriaInterface;

interface AttributeTranslationRepositoryInterface 
{
    public function save(AttributeTranslationInterface $page);

    public function getById($id);

    public function getList(SearchCriteriaInterface $criteria);

    public function delete(AttributeTranslationInterface $page);

    public function deleteById($id);
}
