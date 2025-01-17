<?php

namespace App\Filament\Widgets;

use App\Enums\TransactionTypeEnum;
use App\Models\Category;
use App\Models\Transaction;
use Leandrocfe\FilamentApexCharts\Widgets\ApexChartWidget;

class CategoryChart extends ApexChartWidget
{
    /**
     * Chart Id
     *
     * @var string
     */
    protected static string $chartId = 'categoryChart';

    protected static ?int $sort = 2;

    /**
     * Widget Title
     *
     * @var string|null
     */
    protected static ?string $heading = 'Top Category Transactions';

    protected static ?int $contentHeight = 300;

    /**
     * Chart options (series, labels, types, size, animations...)
     * https://apexcharts.com/docs/options
     *
     * @return array
     */
    protected function getOptions(): array
    {
        $transactions = Transaction::with('category')
            ->whereNotNull('category_id')
            ->tenant()
            ->where('type', TransactionTypeEnum::WITHDRAW->value)
            ->whereBetween('happened_at', [now()->subDays(10)->startOfDay(), now()->endOfDay()])
            ->get()
            ->groupBy(function ($item) {
                return $item->category_id;
            })
            ->map(function ($item) {
                return abs($item->sum('amount') / 100);
            })->sortDesc()->take(10);

        $categories = Category::whereIn('id', $transactions->keys())->pluck('name', 'id')->toArray();
        $data = [];

       foreach ($categories as $key => $category) {
            $data[$category] =  $transactions[$key];
        }

       arsort($data, SORT_NUMERIC);

        return [
            'chart' => [
                'type' => 'bar',
                'height' => 300,
            ],
            'series' => [
                [
                    'name' => '',
                    'data' => array_values($data),
                ],
            ],
            'xaxis' => [
                'categories' => array_keys($data),
                'labels' => [
                    'style' => [
                        'fontFamily' => 'inherit',
                    ],
                ],
            ],
            'yaxis' => [
                'labels' => [
                    'style' => [
                        'fontFamily' => 'inherit',
                    ],
                ],
            ],
            'colors' => ['#f59e0b'],
            'plotOptions' => [
                'bar' => [
                    'borderRadius' => 3,
                    'horizontal' => true,
                ],
            ],
        ];
    }
}
