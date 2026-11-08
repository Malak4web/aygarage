<?php

namespace App\Filament\Widgets;

use App\Models\Booking;
use App\Models\Order;
use App\Models\Product;
use App\Models\UsedBike;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class StatsOverview extends StatsOverviewWidget
{
    protected static ?int $sort = 1;

    protected function getStats(): array
    {
        $todayBookings = Booking::whereDate('date', now()->toDateString())->count();
        $newOrders = Order::where('status', 'new')->count();
        $lowStockProducts = Product::where('stock', '<=', 3)->count();
        $availableBikes = UsedBike::where('status', 'available')->count();

        return [
            Stat::make('حجوزات صيانة اليوم', $todayBookings)
                ->description('مواعيد عمل اليوم في الجراج')
                ->descriptionIcon('heroicon-m-calendar-days')
                ->color($todayBookings > 0 ? 'primary' : 'gray'),

            Stat::make('الطلبات الجديدة', $newOrders)
                ->description('طلبات بانتظار التجهيز')
                ->descriptionIcon('heroicon-m-shopping-cart')
                ->color($newOrders > 0 ? 'warning' : 'gray'),

            Stat::make('منتجات قاربت على النفاد', $lowStockProducts)
                ->description('المخزون 3 قطع أو أقل')
                ->descriptionIcon('heroicon-m-exclamation-triangle')
                ->color($lowStockProducts > 0 ? 'danger' : 'success'),

            Stat::make('موتوسيكلات معروضة للبيع', $availableBikes)
                ->description('موتوسيكل جاهز ومفحوص')
                ->descriptionIcon('heroicon-m-truck')
                ->color('success'),
        ];
    }
}
