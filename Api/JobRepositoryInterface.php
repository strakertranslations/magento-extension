<?php
namespace Straker\EasyTranslationPlatform\Api;

use Straker\EasyTranslationPlatform\Model\JobInterface;
use Magento\Framework\Api\SearchCriteriaInterface;

interface JobRepositoryInterface
{
    public function save(JobInterface $page);

    public function getById($id);

    public function getList(SearchCriteriaInterface $criteria);

    public function delete(JobInterface $page);

    public function deleteById($id);
}
