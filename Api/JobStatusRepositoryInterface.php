<?php
namespace Straker\EasyTranslationPlatform\Api;

use Straker\EasyTranslationPlatform\Model\JobStatusInterface;
use Magento\Framework\Api\SearchCriteriaInterface;

interface JobStatusRepositoryInterface 
{
    public function save(JobStatusInterface $page);

    public function getById($id);

    public function getList(SearchCriteriaInterface $criteria);

    public function delete(JobStatusInterface $page);

    public function deleteById($id);
}
