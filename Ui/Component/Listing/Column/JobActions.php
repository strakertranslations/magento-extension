<?php

namespace Straker\EasyTranslationPlatform\Ui\Component\Listing\Column;

use Magento\Framework\View\Element\UiComponent\ContextInterface;
use Magento\Framework\View\Element\UiComponentFactory;
use Magento\Ui\Component\Listing\Columns\Column;
use Magento\Framework\UrlInterface;

class JobActions extends Column
{
//    /** Url path */
//    const CONTACTS_URL_PATH_EDIT = 'EasyTranslationPlatform/jobs/edit';
//    const CONTACTS_URL_PATH_DELETE = 'EasyTranslationPlatform/jobs/delete';
//
//    /** @var UrlInterface */
//    protected $urlBuilder;
//
//    /**
//     * @var string
//     */
//    private $editUrl;
//
//    /**
//     * @param ContextInterface $context
//     * @param UiComponentFactory $uiComponentFactory
//     * @param UrlInterface $urlBuilder
//     * @param array $components
//     * @param array $data
//     * @param string $editUrl
//     */
//    public function __construct(
//        ContextInterface $context,
//        UiComponentFactory $uiComponentFactory,
//        UrlInterface $urlBuilder,
//        array $components = [],
//        array $data = [],
//        $editUrl = self::CONTACTS_URL_PATH_EDIT
//    ) {
//        $this->urlBuilder = $urlBuilder;
//        $this->editUrl = $editUrl;
//        parent::__construct($context, $uiComponentFactory, $components, $data);
//    }

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
                    if ($statusId == 4) {
                        $item[$name]['confirm'] = [
                            'href' => $this->getContext()->getUrl('EasyTranslationPlatform/Jobs/Confirm',
                                [
                                    'job_id' => $item['job_id'],
                                    'job_key' => $item['job_key'],
                                    'job_type_id' => $item['job_type_id']
                                ]),
                            'label' => __('Confirm')
                        ];
                    }
                    $item[$name]['view'] = [
                        'href' => $this->getContext()->getUrl('EasyTranslationPlatform/Jobs/ViewJob',
                            [
                                'job_id' => $item['job_id'],
                                'job_key' => $item['job_key'],
                                'job_type_id' => $item['job_type_id']
                            ]),
                        'label' => __('View')
                    ];
                }
            }
        }

        return $dataSource;
    }
}
