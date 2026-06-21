<?php

namespace App\Filament\Resources;

use App\Filament\Resources\DataPembeliResource\Pages;
use App\Models\DataPembeli;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Infolists;
use Filament\Infolists\Infolist;
use Illuminate\Database\Eloquent\Builder;
use Filament\Tables\Actions\Action;
use Filament\Notifications\Notification;

class DataPembeliResource extends Resource
{
    protected static ?string $model = DataPembeli::class;
    protected static ?string $navigationIcon = 'heroicon-o-shopping-bag';
    protected static ?string $navigationLabel = 'Pesanan Masuk';
    protected static ?string $modelLabel = 'Pesanan';
    protected static ?string $pluralModelLabel = 'Pesanan';
    protected static ?string $navigationGroup = 'Manajemen Pesanan';
    protected static ?int $navigationSort = 1;

    public static function form(Form $form): Form
    {
        return $form->schema([
            // ── Status & Resi ─────────────────────────────────────────
            Forms\Components\Section::make('Status & Nomor Resi')
                ->description('Kelola status pesanan dan nomor resi pengiriman')
                ->icon('heroicon-o-cog-6-tooth')
                ->schema([
                    Forms\Components\Grid::make(2)
                        ->schema([
                            Forms\Components\Select::make('status')
                                ->label('Status Pesanan')
                                ->options(self::getOrderStatuses())
                                ->required()
                                ->native(false)
                                ->live()
                                ->afterStateUpdated(function ($state, $old) {
                                    if ($state !== $old && $old !== null) {
                                        Notification::make()
                                            ->title('Status berhasil diperbarui')
                                            ->success()
                                            ->send();
                                    }
                                }),

                            Forms\Components\TextInput::make('no_resi')
                                ->label('Nomor Resi JNE')
                                ->placeholder('Otomatis terisi setelah order diproses')
                                ->disabled()
                                ->dehydrated(false)
                                ->suffixIcon('heroicon-o-truck')
                                ->helperText('Resi digenerate otomatis oleh sistem'),
                        ]),

                    Forms\Components\Textarea::make('admin_notes')
                        ->label('Catatan Admin')
                        ->placeholder('Tambahkan catatan internal untuk tim...')
                        ->rows(3)
                        ->columnSpanFull(),
                ]),

            // ── Data Pembeli ───────────────────────────────────────────
            Forms\Components\Section::make('Data Pembeli')
                ->icon('heroicon-o-user-circle')
                ->schema([
                    Forms\Components\Grid::make(3)
                        ->schema([
                            Forms\Components\TextInput::make('name')
                                ->label('Nama Pembeli')
                                ->disabled()
                                ->dehydrated(false),

                            Forms\Components\TextInput::make('email')
                                ->label('Email')
                                ->disabled()
                                ->dehydrated(false),

                            Forms\Components\TextInput::make('phone')
                                ->label('Nomor Telepon')
                                ->disabled()
                                ->dehydrated(false)
                                ->suffixAction(
                                    Forms\Components\Actions\Action::make('whatsapp')
                                        ->icon('heroicon-o-chat-bubble-bottom-center-text')
                                        ->url(fn ($record) => "https://wa.me/" . preg_replace('/[^0-9]/', '', $record?->phone))
                                        ->openUrlInNewTab()
                                        ->tooltip('Chat WhatsApp')
                                ),
                        ]),
                ])
                ->collapsible()
                ->collapsed(),

            // ── Alamat Pengiriman ──────────────────────────────────────
            Forms\Components\Section::make('Alamat Pengiriman')
                ->icon('heroicon-o-map-pin')
                ->schema([
                    Forms\Components\Textarea::make('address')
                        ->label('Alamat')
                        ->disabled()
                        ->dehydrated(false)
                        ->rows(2)
                        ->columnSpanFull(),

                    Forms\Components\Grid::make(4)
                        ->schema([
                            Forms\Components\TextInput::make('subdistrict')
                                ->label('Kelurahan')
                                ->disabled()
                                ->dehydrated(false),

                            Forms\Components\TextInput::make('district')
                                ->label('Kecamatan')
                                ->disabled()
                                ->dehydrated(false),

                            Forms\Components\TextInput::make('regency')
                                ->label('Kab/Kota')
                                ->disabled()
                                ->dehydrated(false),

                            Forms\Components\TextInput::make('province')
                                ->label('Provinsi')
                                ->disabled()
                                ->dehydrated(false),
                        ]),

                    Forms\Components\TextInput::make('zip_code')
                        ->label('Kode Pos')
                        ->disabled()
                        ->dehydrated(false),
                ])
                ->collapsible()
                ->collapsed(),

            // ── Detail Pesanan ─────────────────────────────────────────
            Forms\Components\Section::make('Detail Pesanan')
                ->icon('heroicon-o-shopping-cart')
                ->schema([
                    Forms\Components\Grid::make(3)
                        ->schema([
                            Forms\Components\TextInput::make('order_number')
                                ->label('Nomor Order')
                                ->disabled()
                                ->dehydrated(false),

                            Forms\Components\Select::make('payment_method')
                                ->label('Metode Pembayaran')
                                ->options(self::getPaymentMethods())
                                ->disabled()
                                ->dehydrated(false),

                            Forms\Components\Select::make('shipping_method')
                                ->label('Metode Pengiriman')
                                ->options(self::getShippingMethods())
                                ->disabled()
                                ->dehydrated(false),
                        ]),

                    Forms\Components\Grid::make(2)
                        ->schema([
                            Forms\Components\Toggle::make('use_insurance')
                                ->label('Menggunakan Asuransi')
                                ->disabled()
                                ->dehydrated(false),

                            Forms\Components\Toggle::make('is_buy_now')
                                ->label('Pembelian Langsung (Buy Now)')
                                ->disabled()
                                ->dehydrated(false),
                        ]),
                ])
                ->collapsible()
                ->collapsed(),

            // ── Ringkasan Pembayaran ───────────────────────────────────
            Forms\Components\Section::make('Ringkasan Pembayaran')
                ->icon('heroicon-o-banknotes')
                ->schema([
                    Forms\Components\Grid::make(4)
                        ->schema([
                            Forms\Components\TextInput::make('subtotal_before_voucher')
                                ->label('Subtotal Produk')
                                ->disabled()
                                ->dehydrated(false)
                                ->prefix('Rp ')
                                ->formatStateUsing(fn ($state) => number_format($state ?? 0)),

                            Forms\Components\TextInput::make('total_voucher_discount')
                                ->label('Diskon Voucher')
                                ->disabled()
                                ->dehydrated(false)
                                ->prefix('Rp ')
                                ->formatStateUsing(fn ($state) => number_format($state ?? 0)),

                            Forms\Components\TextInput::make('shipping_cost')
                                ->label('Biaya Pengiriman')
                                ->disabled()
                                ->dehydrated(false)
                                ->prefix('Rp ')
                                ->formatStateUsing(fn ($state) => number_format($state ?? 0)),

                            Forms\Components\TextInput::make('insurance_cost')
                                ->label('Biaya Asuransi')
                                ->disabled()
                                ->dehydrated(false)
                                ->prefix('Rp ')
                                ->formatStateUsing(fn ($state) => number_format($state ?? 0)),
                        ]),

                    Forms\Components\TextInput::make('grand_total')
                        ->label('TOTAL TAGIHAN')
                        ->disabled()
                        ->dehydrated(false)
                        ->prefix('Rp ')
                        ->formatStateUsing(fn ($state) => number_format($state ?? 0))
                        ->extraAttributes(['class' => 'font-bold text-xl'])
                        ->columnSpanFull(),
                ])
                ->collapsible()
                ->collapsed(),

            // ── Catatan Pembeli ────────────────────────────────────────
            Forms\Components\Section::make('Catatan Pembeli')
                ->icon('heroicon-o-chat-bubble-left-ellipsis')
                ->schema([
                    Forms\Components\Textarea::make('notes')
                        ->label('Pesan dari Pembeli')
                        ->disabled()
                        ->dehydrated(false)
                        ->placeholder('Tidak ada catatan khusus')
                        ->rows(2),
                ])
                ->collapsible()
                ->collapsed(),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('order_number')
                    ->label('No. Order')
                    ->searchable()
                    ->sortable()
                    ->copyable()
                    ->weight('bold')
                    ->size('sm'),

                Tables\Columns\TextColumn::make('name')
                    ->label('Pembeli')
                    ->searchable()
                    ->sortable()
                    ->wrap(),

                Tables\Columns\TextColumn::make('regency')
                    ->label('Kota Tujuan')
                    ->searchable()
                    ->size('sm')
                    ->description(fn ($record) => $record->province),

                Tables\Columns\BadgeColumn::make('status')
                    ->label('Status')
                    ->colors([
                        'danger'  => 'pending',
                        'warning' => 'paid',
                        'info'    => 'processing',
                        'primary' => 'shipped',
                        'success' => 'delivered',
                        'gray'    => 'cancelled',
                    ])
                    ->formatStateUsing(fn (string $state): string =>
                        self::getOrderStatuses()[$state] ?? $state
                    ),

                Tables\Columns\TextColumn::make('payment_method')
                    ->label('Pembayaran')
                    ->formatStateUsing(fn (string $state): string =>
                        self::getPaymentMethods()[$state] ?? strtoupper($state)
                    )
                    ->badge()
                    ->color('info')
                    ->size('sm'),

                Tables\Columns\TextColumn::make('no_resi')
                    ->label('No Resi')
                    ->placeholder('—')
                    ->searchable()
                    ->copyable()
                    ->copyMessage('Nomor resi disalin!')
                    ->icon('heroicon-o-truck')
                    ->color(fn ($record) => $record->no_resi ? 'success' : 'gray')
                    ->size('sm'),

                Tables\Columns\TextColumn::make('grand_total')
                    ->label('Total')
                    ->money('IDR')
                    ->sortable()
                    ->weight('bold')
                    ->alignEnd()
                    ->color('success')
                    ->size('sm'),

                Tables\Columns\TextColumn::make('created_at')
                    ->label('Waktu Pesan')
                    ->dateTime('d/m H:i')
                    ->sortable()
                    ->since()
                    ->tooltip(fn ($record) => $record->created_at->format('l, d F Y H:i:s'))
                    ->size('sm'),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->label('Status')
                    ->options(self::getOrderStatuses())
                    ->multiple()
                    ->preload(),

                Tables\Filters\SelectFilter::make('payment_method')
                    ->label('Metode Pembayaran')
                    ->options(self::getPaymentMethods())
                    ->multiple(),

                Tables\Filters\Filter::make('today')
                    ->label('Pesanan Hari Ini')
                    ->query(fn (Builder $query): Builder => $query->whereDate('created_at', today()))
                    ->toggle(),

                Tables\Filters\Filter::make('this_week')
                    ->label('Minggu Ini')
                    ->query(fn (Builder $query): Builder => $query->whereBetween('created_at', [
                        now()->startOfWeek(),
                        now()->endOfWeek()
                    ]))
                    ->toggle(),

                Tables\Filters\Filter::make('no_resi_empty')
                    ->label('Belum Ada Resi')
                    ->query(fn (Builder $query): Builder => $query->whereNull('no_resi'))
                    ->toggle(),

                Tables\Filters\Filter::make('high_value')
                    ->label('Pesanan > 500K')
                    ->query(fn (Builder $query): Builder => $query->where('grand_total', '>', 500000))
                    ->toggle(),

                Tables\Filters\TernaryFilter::make('is_buy_now')
                    ->label('Tipe Pembelian')
                    ->trueLabel('Buy Now')
                    ->falseLabel('Keranjang')
                    ->native(false),
            ])
            ->actions([
                Tables\Actions\ActionGroup::make([
                    Tables\Actions\Action::make('mark_paid')
                        ->label('Tandai Dibayar')
                        ->icon('heroicon-o-check-circle')
                        ->color('success')
                        ->visible(fn ($record) => $record->status === 'pending')
                        ->requiresConfirmation()
                        ->action(function ($record) {
                            $record->update(['status' => 'paid']);
                            Notification::make()
                                ->title('Status diupdate ke "Sudah Dibayar"')
                                ->success()
                                ->send();
                        }),

                    Tables\Actions\Action::make('mark_processing')
                        ->label('Mulai Proses')
                        ->icon('heroicon-o-cog-6-tooth')
                        ->color('info')
                        ->visible(fn ($record) => $record->status === 'paid')
                        ->requiresConfirmation()
                        ->action(function ($record) {
                            $record->update(['status' => 'processing']);
                            Notification::make()
                                ->title('Pesanan mulai diproses')
                                ->success()
                                ->send();
                        }),

                    Tables\Actions\Action::make('mark_shipped')
                        ->label('Tandai Dikirim')
                        ->icon('heroicon-o-truck')
                        ->color('primary')
                        ->visible(fn ($record) => $record->status === 'processing')
                        ->requiresConfirmation()
                        ->action(function ($record) {
                            $record->update(['status' => 'shipped']);
                            Notification::make()
                                ->title('Pesanan ditandai dikirim')
                                ->success()
                                ->send();
                        }),

                    Tables\Actions\Action::make('mark_delivered')
                        ->label('Tandai Diterima')
                        ->icon('heroicon-o-check-badge')
                        ->color('success')
                        ->visible(fn ($record) => $record->status === 'shipped')
                        ->requiresConfirmation()
                        ->action(function ($record) {
                            $record->update(['status' => 'delivered']);
                            Notification::make()
                                ->title('Pesanan sudah diterima')
                                ->success()
                                ->send();
                        }),

                    Tables\Actions\Action::make('cancel_order')
                        ->label('Batalkan Pesanan')
                        ->icon('heroicon-o-x-circle')
                        ->color('danger')
                        ->visible(fn ($record) => !in_array($record->status, ['delivered', 'cancelled']))
                        ->requiresConfirmation()
                        ->modalHeading('Batalkan Pesanan?')
                        ->modalDescription('Tindakan ini tidak dapat dibatalkan.')
                        ->action(function ($record) {
                            $record->update(['status' => 'cancelled']);
                            Notification::make()
                                ->title('Pesanan dibatalkan')
                                ->danger()
                                ->send();
                        }),
                ])
                ->label('Aksi')
                ->icon('heroicon-o-ellipsis-vertical')
                ->size('sm')
                ->color('gray'),

                Tables\Actions\EditAction::make()
                    ->label('Kelola')
                    ->size('sm'),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\BulkAction::make('bulk_paid')
                        ->label('Tandai Dibayar')
                        ->icon('heroicon-o-check-circle')
                        ->color('success')
                        ->requiresConfirmation()
                        ->action(function ($records) {
                            $records->each->update(['status' => 'paid']);
                            Notification::make()
                                ->title(count($records) . ' pesanan ditandai dibayar')
                                ->success()
                                ->send();
                        }),

                    Tables\Actions\BulkAction::make('bulk_processing')
                        ->label('Mulai Proses Semua')
                        ->icon('heroicon-o-cog-6-tooth')
                        ->color('info')
                        ->requiresConfirmation()
                        ->action(function ($records) {
                            $records->each->update(['status' => 'processing']);
                            Notification::make()
                                ->title(count($records) . ' pesanan mulai diproses')
                                ->success()
                                ->send();
                        }),

                    Tables\Actions\DeleteBulkAction::make()
                        ->requiresConfirmation(),
                ]),
            ])
            ->defaultSort('created_at', 'desc')
            ->striped()
            ->paginated([25, 50, 100])
            ->poll('30s')
            ->persistFiltersInSession()
            ->persistSortInSession();
    }

    public static function infolist(Infolist $infolist): Infolist
    {
        return $infolist->schema([
            // ── Header Status ──────────────────────────────────────────
            Infolists\Components\Section::make('Ringkasan Pesanan')
                ->icon('heroicon-o-clipboard-document-check')
                ->schema([
                    Infolists\Components\Grid::make(4)
                        ->schema([
                            Infolists\Components\TextEntry::make('order_number')
                                ->label('Nomor Order')
                                ->weight('bold')
                                ->size('lg')
                                ->copyable(),

                            Infolists\Components\TextEntry::make('status')
                                ->label('Status')
                                ->badge()
                                ->size('lg')
                                ->color(fn (string $state): string => match ($state) {
                                    'pending'    => 'danger',
                                    'paid'       => 'warning',
                                    'processing' => 'info',
                                    'shipped'    => 'primary',
                                    'delivered'  => 'success',
                                    'cancelled'  => 'gray',
                                    default      => 'gray',
                                })
                                ->formatStateUsing(fn (string $state): string =>
                                    self::getOrderStatuses()[$state] ?? $state
                                ),

                            Infolists\Components\TextEntry::make('no_resi')
                                ->label('Nomor Resi JNE')
                                ->placeholder('Belum digenerate')
                                ->copyable()
                                ->copyMessage('Resi disalin!')
                                ->icon('heroicon-o-truck')
                                ->color(fn ($record) => $record->no_resi ? 'success' : 'gray')
                                ->weight('bold'),

                            Infolists\Components\TextEntry::make('created_at')
                                ->label('Waktu Pesan')
                                ->dateTime('d F Y, H:i')
                                ->since(),
                        ]),
                ]),

            // ── Informasi Pembeli ──────────────────────────────────────
            Infolists\Components\Section::make('Informasi Pembeli')
                ->icon('heroicon-o-user-circle')
                ->schema([
                    Infolists\Components\Grid::make(3)
                        ->schema([
                            Infolists\Components\TextEntry::make('name')
                                ->label('Nama')
                                ->icon('heroicon-o-user')
                                ->weight('medium')
                                ->copyable(),

                            Infolists\Components\TextEntry::make('email')
                                ->label('Email')
                                ->icon('heroicon-o-envelope')
                                ->copyable(),

                            Infolists\Components\TextEntry::make('phone')
                                ->label('Telepon')
                                ->icon('heroicon-o-phone')
                                ->copyable()
                                ->url(fn ($record) => "https://wa.me/" . preg_replace('/[^0-9]/', '', $record->phone))
                                ->openUrlInNewTab()
                                ->color('success'),
                        ]),

                    Infolists\Components\TextEntry::make('full_address')
                        ->label('Alamat Lengkap')
                        ->icon('heroicon-o-map-pin')
                        ->columnSpanFull()
                        ->copyable(),
                ]),

            // ── Pembayaran & Pengiriman ────────────────────────────────
            Infolists\Components\Section::make('Pembayaran & Pengiriman')
                ->icon('heroicon-o-credit-card')
                ->schema([
                    Infolists\Components\Grid::make(3)
                        ->schema([
                            Infolists\Components\TextEntry::make('payment_method')
                                ->label('Metode Pembayaran')
                                ->formatStateUsing(fn (string $state): string =>
                                    self::getPaymentMethods()[$state] ?? strtoupper($state)
                                )
                                ->badge()
                                ->color('info'),

                            Infolists\Components\TextEntry::make('shipping_method')
                                ->label('Metode Pengiriman')
                                ->formatStateUsing(fn (string $state): string =>
                                    self::getShippingMethods()[$state] ?? strtoupper($state)
                                )
                                ->badge()
                                ->color('primary'),

                            Infolists\Components\TextEntry::make('jne_service_code')
                                ->label('Layanan JNE')
                                ->placeholder('—')
                                ->badge()
                                ->color('warning'),
                        ]),

                    Infolists\Components\Grid::make(2)
                        ->schema([
                            Infolists\Components\IconEntry::make('use_insurance')
                                ->label('Asuransi Pengiriman')
                                ->boolean()
                                ->trueIcon('heroicon-o-shield-check')
                                ->falseIcon('heroicon-o-shield-exclamation')
                                ->trueColor('success'),

                            Infolists\Components\IconEntry::make('is_buy_now')
                                ->label('Tipe Pembelian')
                                ->boolean()
                                ->trueIcon('heroicon-o-bolt')
                                ->falseIcon('heroicon-o-shopping-cart')
                                ->trueColor('warning')
                                ->falseColor('info'),
                        ]),
                ]),

            // ── Detail Produk ──────────────────────────────────────────
            Infolists\Components\Section::make('Detail Produk')
                ->icon('heroicon-o-cube')
                ->schema([
                    Infolists\Components\RepeatableEntry::make('items')
                        ->label('')
                        ->schema([
                            Infolists\Components\Grid::make(5)
                                ->schema([
                                    Infolists\Components\TextEntry::make('name')
                                        ->label('Nama Produk')
                                        ->weight('medium')
                                        ->columnSpan(2),

                                    Infolists\Components\TextEntry::make('sku')
                                        ->label('SKU')
                                        ->placeholder('—')
                                        ->color('gray')
                                        ->size('sm'),

                                    Infolists\Components\TextEntry::make('quantity')
                                        ->label('Qty')
                                        ->suffix(' pcs')
                                        ->weight('medium')
                                        ->alignCenter(),

                                    Infolists\Components\TextEntry::make('price')
                                        ->label('Harga')
                                        ->money('IDR')
                                        ->weight('medium')
                                        ->alignEnd(),
                                ]),
                        ])
                        ->contained(false),
                ]),

            // ── Ringkasan Keuangan ─────────────────────────────────────
            Infolists\Components\Section::make('Ringkasan Keuangan')
                ->icon('heroicon-o-banknotes')
                ->schema([
                    Infolists\Components\Grid::make(4)
                        ->schema([
                            Infolists\Components\TextEntry::make('subtotal_before_voucher')
                                ->label('Subtotal Produk')
                                ->money('IDR'),

                            Infolists\Components\TextEntry::make('total_voucher_discount')
                                ->label('Diskon Voucher')
                                ->money('IDR')
                                ->color('success'),

                            Infolists\Components\TextEntry::make('shipping_cost')
                                ->label('Biaya Pengiriman')
                                ->money('IDR'),

                            Infolists\Components\TextEntry::make('insurance_cost')
                                ->label('Biaya Asuransi')
                                ->money('IDR'),
                        ]),

                    Infolists\Components\Separator::make(),

                    Infolists\Components\TextEntry::make('grand_total')
                        ->label('TOTAL PEMBAYARAN')
                        ->money('IDR')
                        ->size(Infolists\Components\TextEntry\TextEntrySize::Large)
                        ->weight('bold')
                        ->color('primary')
                        ->columnSpanFull(),
                ]),

            // ── Catatan & Riwayat ──────────────────────────────────────
            Infolists\Components\Section::make('Catatan & Riwayat')
                ->icon('heroicon-o-document-text')
                ->schema([
                    Infolists\Components\TextEntry::make('notes')
                        ->label('Catatan Pembeli')
                        ->placeholder('Tidak ada catatan')
                        ->columnSpanFull()
                        ->color('gray'),

                    Infolists\Components\TextEntry::make('admin_notes')
                        ->label('Catatan Admin')
                        ->placeholder('Belum ada catatan admin')
                        ->columnSpanFull()
                        ->color('info'),

                    Infolists\Components\Grid::make(2)
                        ->schema([
                            Infolists\Components\TextEntry::make('created_at')
                                ->label('Waktu Order Masuk')
                                ->dateTime('l, d F Y H:i:s'),

                            Infolists\Components\TextEntry::make('updated_at')
                                ->label('Terakhir Diupdate')
                                ->dateTime('l, d F Y H:i:s')
                                ->since(),
                        ]),
                ])
                ->collapsible()
                ->collapsed(),
        ]);
    }

    protected static function getPaymentMethods(): array
    {
        return [
            'cod'        => 'Cash on Delivery',
            'mandiri'    => 'Mandiri Virtual Account',
            'bri'        => 'BRI Virtual Account',
            'bni'        => 'BNI Virtual Account',
            'permata'    => 'Permata Virtual Account',
            'CIMB NIAGA' => 'CIMB Niaga Virtual Account',
            'gopay'      => 'GoPay',
            'qris'       => 'QRIS',
        ];
    }

    protected static function getShippingMethods(): array
    {
        return [
            'reguler' => 'Reguler (Gratis)',
            'ninja'   => 'Ninja Xpress',
            'jne'     => 'JNE',
        ];
    }

    protected static function getOrderStatuses(): array
    {
        return [
            'pending'    => 'Menunggu Pembayaran',
            'paid'       => 'Sudah Dibayar',
            'processing' => 'Sedang Diproses',
            'shipped'    => 'Dalam Pengiriman',
            'delivered'  => 'Sudah Diterima',
            'cancelled'  => 'Dibatalkan',
        ];
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListDataPembelis::route('/'),
            'edit'  => Pages\EditDataPembeli::route('/{record}/edit'),
        ];
    }

    public static function getGlobalSearchAttributes(): array
    {
        return ['name', 'phone', 'order_number', 'no_resi'];
    }

    public static function getGlobalSearchResultTitle($record): string
    {
        return "Pesanan {$record->order_number} - {$record->name}";
    }

    public static function getGlobalSearchResultDetails($record): array
    {
        return [
            'Status' => self::getOrderStatuses()[$record->status] ?? $record->status,
            'Total'  => 'Rp ' . number_format($record->grand_total),
            'Resi'   => $record->no_resi ?? 'Belum ada',
            'Tanggal' => $record->created_at->format('d M Y'),
        ];
    }
}