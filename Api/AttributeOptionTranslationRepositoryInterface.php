<?php
namespace Straker\EasyTranslationPlatform\Api;

use Straker\EasyTranslationPlatform\Model\AttributeOptionTranslationInterface;
use Magento\Framework\Api\SearchCriteriaInterface;

interface AttributeOptionTranslationRepositoryInterface 
{
    public function save(AttributeOptionTranslationInterface $page);

    public function getById($id);

    public function getList(SearchCriteriaInterface $criteria);

    public function delete(AttributeOptionTranslationInterface $page);

    public function deleteById($id);
}
