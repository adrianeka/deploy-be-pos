<?php

namespace App\Filament\Resources\RiwayatZakatResource\RelationManagers;

use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Tables\Actions\Action;
use Illuminate\Database\Eloquent\Builder;

class PenjualanRelationManager extends RelationManager
{
    protected static string $relationship = 'penjualan';

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('id_penjualan')
                    ->required(),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('id_penjualan')
            ->columns([
                Tables\Columns\TextColumn::make('id_penjualan')
                    ->label('Nomor Invoice'),
                Tables\Columns\TextColumn::make('modalTerjual')
                    ->label('Modal Terjual')
                    ->formatStateUsing(fn($state) => 'Rp. ' . number_format($state, 0, ',', '.')),
                Tables\Columns\TextColumn::make('zakat')
                    ->label('Zakat (2.5%)')
                    ->formatStateUsing(fn($state) => 'Rp. ' . number_format($state, 0, ',', '.')),                
                Tables\Columns\TextColumn::make('tanggal_penjualan')
                    ->label('Tanggal')
                    ->formatStateUsing(fn($state) => \Carbon\Carbon::parse($state)->translatedFormat('d M Y, \\J\\a\\m H:i')),
            ])
            ->actions([
                Action::make('View')
                    ->icon('heroicon-o-eye')
                    ->url(fn ($record) => route('filament.admin.resources.riwayat-penjualans.view', ['record' => $record->id_penjualan]))
                    // ->openUrlInNewTab(), // atau hapus kalo mau di tab yg sama
            ]);
    }
}
