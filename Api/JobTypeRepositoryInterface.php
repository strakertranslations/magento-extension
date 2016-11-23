<?php
namespace Straker\EasyTranslationPlatform\Api;

use Straker\EasyTranslationPlatform\Model\JobTypeInterface;
use Magento\Framework\Api\SearchCriteriaInterface;

interface JobTypeRepositoryInterface
{
    public function save(JobTypeInterface $page);

    public function getById($id);

    public function getList(SearchCriteriaInterface $criteria);

    public function delete(JobTypeInterface $page);

    public function deleteById($id);
}
