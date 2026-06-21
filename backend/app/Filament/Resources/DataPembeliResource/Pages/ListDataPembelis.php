<?php

namespace App\Filament\Resources\DataPembeliResource\Pages;

use App\Filament\Resources\DataPembeliResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;
use Filament\Support\Enums\MaxWidth;

class ListDataPembelis extends ListRecords
{
    protected static string $resource = DataPembeliResource::class;

    // PENTING: ini yang membuat konten ikut melebar penuh saat sidebar
    // ditutup. Tanpa ini, Filament membatasi lebar konten ke nilai default
    // (biasanya 7xl) yang tidak menyesuaikan ruang kosong dari sidebar.
    public function getMaxContentWidth(): MaxWidth|string|null
    {
        return MaxWidth::Full;
    }

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}