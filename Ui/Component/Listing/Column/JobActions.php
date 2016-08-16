<?php

namespace Straker\EasyTranslationPlatform\Ui\Component\Listing\Column;

use Magento\Ui\Component\Listing\Columns\Column;
use Straker\EasyTranslationPlatform\Model;

class JobActions extends Column
{
    /**
     * Prepare Data Source
     *
     * @param array $dataSource
     * @return array
     */
    public function prepareDataSource(array $dataSource)
    {
        if (isset($dataSource['data']['items'])) {
            foreach ($dataSource['data']['items'] as & $item) {
                $name = $this->getData('name');
                if (array_key_exists('job_status_id', $item) && array_key_exists('job_id', $item)) {
                    $statusId = $item['job_status_id'];

                    $url = 'EasyTranslationPlatform/Jobs/ViewJob';

                    $item[$name]['view'] = [
                        'href' => $this->getContext()
                            ->getUrl(
                                $url,
                                [
                                    'job_id' => $item['job_id'],
                                    'job_key' => $item['job_key'],
                                    'job_type_id' => $item['job_type_id'],
                                    'source_store_id' => $item['source_store_id']
                                ]
                            ),
                        'label' => __('View')
                    ];

                    if( $statusId >= Model\JobStatus::JOB_STATUS_READY ){
                        $item[$name]['view_quote'] = [
                            'href' => 'javascript:void(0)',
                            'label' => __('View Quote')
                        ];
                    }

                    if ($statusId == Model\JobStatus::JOB_STATUS_COMPLETED) {
                        $item[$name]['confirm'] = [
                            'href' => $this->getContext()->getUrl('EasyTranslationPlatform/Jobs/Confirm',
                                ['job_id' => $item['job_id'], 'job_key' => $item['job_key'], 'job_type_id' => $item['job_type_id']]),
                            'label' => __('Confirm')
                        ];
                    }

//                    if ($statusId == Model\JobStatus::JOB_STATUS_CONFIRMED){
//                        $item[$name]['publish'] = [
//                            'href' => $this->getContext()->getUrl('EasyTranslationPlatform/Jobs/Publish',
//                                ['job_id' => $item['job_id'], 'job_key' => $item['job_key'], 'job_type_id' => $item['job_type_id']]),
//                            'label' => __('Publish')
//                        ];
//                    }


                }
            }
        }

        return $dataSource;
    }
}
