<?php

namespace App\Filament\Widgets;

use App\Enums\TransactionTypeEnum;
use App\Models\Transaction;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Support\Number;

class TotalBalance extends BaseWidget
{
    protected static ?string $pollingInterval = '30s';



    protected function getStats(): array
    {

        $ingresos = Transaction::where([
                ['happened_at', '<=', now()],
                ['confirmed', true],
            ])->whereIn('type', ['deposit', 'transfer'])->sum('amount') / 100;

        $egresos = Transaction::where([
            ['happened_at', '<=', now()],
            ['confirmed', true],
        ])->whereIn('type', ['payment', 'withdraw'])->sum('amount') / 100;


        return [
              Stat::make('Total', Number::currency(number: $ingresos - abs($egresos), in: 'MXN' ))
                  ->icon('polaris-major-cash-dollar'),
              Stat::make('Ingresos', Number::currency(number: $ingresos, in: 'MXN'))
                  ->icon('lucide-trending-up')
                  ->color('warning'),
              Stat::make('Egresos', Number::currency(abs($egresos), in: 'MXN'))
                  ->icon('lucide-trending-down')
                  ->color('warning'),
        ];
    }
}
