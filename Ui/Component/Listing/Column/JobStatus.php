<?php

namespace Straker\EasyTranslationPlatform\Ui\Component\Listing\Column;

use Magento\Ui\Component\Listing\Columns\Column;

class JobStatus extends Column
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
            foreach ($dataSource['data']['items'] as &$item) {
                if (array_key_exists('job_status_name', $item)) {
                    switch (strtolower($item[$this->getData('name')])) {
                        case 'init':
                            $item[$this->getData('name')] = __(ucwords('waiting for job number'));
                            break;
                        case 'queued':
                            $item[$this->getData('name')] = __(ucwords('waiting for quote'));
                            break;
                        case 'ready':
                            $item[$this->getData('name')] = "<a href='#' class='straker-view-quote-anchor' data-job-id='". $item['job_id'] ."' data-job-key='" . $item['job_key'] . "' >" . __('View Quote ') . "</a>";
                            break;
                        case 'in_progress':
                            $item[$this->getData('name')] = __(ucwords('in progress'));
                            break;
                        case 'completed':
                            $item[$this->getData('name')] = __(ucwords('ready to publish'));
                            break;
                        case 'published':
                            $item[$this->getData('name')] = __(ucwords($item[$this->getData('name')]));
                            break;
                    }
                }
            }
        }
        return $dataSource;
    }
}
