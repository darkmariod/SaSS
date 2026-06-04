<?php

namespace App\Filament\Pages;

use BackedEnum;
use Filament\Pages\Page;

class CalendarView extends Page
{
    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-calendar-days';

    protected static ?string $navigationLabel = 'Calendario';

    protected static ?string $title = 'Calendario de reservas';

    protected static ?string $slug = 'calendar';

    protected static ?int $navigationSort = 2;

    public static function canAccess(): bool
    {
        return auth()->user()?->hasAnyRole(['owner', 'admin', 'barber']) ?? false;
    }

    public function getView(): string
    {
        return 'filament.pages.calendar-view';
    }
}
