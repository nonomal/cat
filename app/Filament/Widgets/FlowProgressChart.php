<?php

namespace App\Filament\Widgets;

use App\Services\FlowHasFormService;
use App\Utils\UrlUtil;
use Leandrocfe\FilamentApexCharts\Widgets\ApexChartWidget;

class FlowProgressChart extends ApexChartWidget
{
    /**
     * Chart Id
     *
     * @var string
     */
    protected static string $chartId = 'flowProgressChart';

    /**
     * Widget Title
     *
     * @var string|null
     */
    protected static ?string $heading = '流程路径';
    protected static ?string $pollingInterval = null;

    /**
     * Chart options (series, labels, types, size, animations...)
     * https://apexcharts.com/docs/options
     *
     * @return array
     */
    protected function getOptions(): array
    {
        $form_id = UrlUtil::getRecordId();
        $flow_has_form_service = new FlowHasFormService();
        $flow_has_form_service->setFlowHasFormByFormId($form_id);
        $nodes = $flow_has_form_service->sortNodes();
        return [
            'chart' => [
                'type' => 'heatmap',
                'height' => 80,
                'width' => '90%',
                'toolbar' => [
                    'show' => false
                ]
            ],
            'series' => [
                ['data' => $nodes['id']],
            ],
            'xaxis' => [
                'type' => 'category',
                'labels' => [
                    'show' => true,
                    'style' => [
                        'fontFamily' => 'inherit',
                    ],
                ],
                'categories' => $nodes['name'],
            ],
            'yaxis' => [
                'labels' => [
                    'show' => false,
                ],
            ],
            'dataLabels' => [
                'enabled' => false,
            ],
            'colors' => ['#3B82F6'],
            'tooltip' => [
                'enabled' => false
            ]
        ];
    }
}