<?php

namespace App\Filament\Resources;

use App\Filament\Actions\DeviceAction;
use App\Filament\Forms\DeviceForm;
use App\Filament\Imports\DeviceImporter;
use App\Filament\Resources\DeviceResource\Pages\Create;
use App\Filament\Resources\DeviceResource\Pages\Edit;
use App\Filament\Resources\DeviceResource\Pages\Index;
use App\Filament\Resources\DeviceResource\Pages\View;
use App\Filament\Resources\DeviceResource\RelationManagers\HasPartRelationManager;
use App\Filament\Resources\DeviceResource\RelationManagers\HasSoftwareRelationManager;
use App\Filament\Resources\DeviceResource\RelationManagers\HasUserRelationManager;
use App\Http\Middleware\FilamentLockTab;
use App\Models\Device;
use App\Services\DeviceCategoryService;
use App\Services\DeviceService;
use BezhanSalleh\FilamentShield\Contracts\HasShieldPermissions;
use Exception;
use Filament\Forms\Form;
use Filament\Infolists\Components\Grid;
use Filament\Infolists\Components\Group;
use Filament\Infolists\Components\ImageEntry;
use Filament\Infolists\Components\Section;
use Filament\Infolists\Components\Split;
use Filament\Infolists\Components\TextEntry;
use Filament\Infolists\Infolist;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Actions\ImportAction;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;
use pxlrbt\FilamentExcel\Actions\Tables\ExportAction;

class DeviceResource extends Resource implements HasShieldPermissions
{
    protected static ?string $model = Device::class;

    protected static ?string $navigationIcon = 'heroicon-s-server';

    protected static ?string $modelLabel = '设备';

    protected static ?int $navigationSort = 1;

    protected static ?string $navigationGroup = '资产';

    protected static string|array $routeMiddleware = FilamentLockTab::class;

    public static function getPermissionPrefixes(): array
    {
        return [
            'view',
            'view_any',
            'create',
            'update',
            'delete',
            'delete_any',
            'assign_user',
            'delete_assign_user',
            'import',
            'export',
            'retire',
            'force_retire',
        ];
    }

    /**
     * @throws Exception
     */
    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\ImageColumn::make('image')
                    ->label('照片')
                    ->toggleable(),
                Tables\Columns\TextColumn::make('asset_number')
                    ->searchable()
                    ->label('资产编号'),
                Tables\Columns\TextColumn::make('name')
                    ->label('名称')
                    ->toggleable(),
                Tables\Columns\TextColumn::make('brand.name')
                    ->label('品牌')
                    ->toggleable(),
                Tables\Columns\TextColumn::make('category.name')
                    ->label('分类')
                    ->toggleable(),
                Tables\Columns\TextColumn::make('users.name')
                    ->searchable()
                    ->label('管理者')
                    ->toggleable(),
                Tables\Columns\TextColumn::make('specification')
                    ->searchable()
                    ->label('规格')
                    ->toggleable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('category_id')
                    ->multiple()
                    ->options(DeviceCategoryService::pluckOptions())
                    ->label('分类'),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
                Tables\Actions\ActionGroup::make([
                    // 分配管理者
                    DeviceAction::createDeviceHasUser()
                        ->visible(function (Device $device) {
                            $can = auth()->user()->can('assign_user_device');

                            return $can && ! $device->hasUsers()->count();
                        }),
                    // 解除管理者
                    DeviceAction::deleteDeviceHasUser()
                        ->visible(function (Device $device) {
                            $can = auth()->user()->can('delete_assign_user_device');

                            return $can && $device->hasUsers()->count();
                        }),
                    // 流程报废
                    DeviceAction::retireDevice()
                        ->visible(function () {
                            $can = auth()->user()->can('retire_device');

                            return $can && DeviceService::isSetRetireFlow();
                        }),
                    // 强制报废
                    DeviceAction::forceRetireDevice()
                        ->visible(function () {
                            return auth()->user()->can('force_retire_device');
                        }),
                ]),
            ])
            ->bulkActions([

            ])
            ->emptyStateActions([

            ])
            ->headerActions([
                ImportAction::make()
                    ->importer(DeviceImporter::class)
                    ->icon('heroicon-o-arrow-up-tray')
                    ->color('info')
                    ->label('导入')
                    ->visible(auth()->user()->can('import_device')),
                ExportAction::make()
                    ->label('导出')
                    ->visible(auth()->user()->can('export_device')),
                DeviceAction::createDevice(),
                Tables\Actions\ActionGroup::make([
                    DeviceAction::setAssetNumberRule(),
                    DeviceAction::resetAssetNumberRule(),
                    DeviceAction::setDeviceRetireFlowId(),
                ])
                    ->label('高级')
                    ->icon('heroicon-m-cog-8-tooth')
                    ->button(),
            ]);
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema(DeviceForm::createOrEditDevice());
    }

    public static function infolist(Infolist $infolist): Infolist
    {
        return $infolist->schema([
            Group::make()->schema([
                Section::make()
                    ->schema([
                        Split::make([
                            Grid::make()
                                ->schema([
                                    Group::make([
                                        TextEntry::make('asset_number')
                                            ->label('资产编号')
                                            ->badge()
                                            ->color('primary'),
                                        TextEntry::make('name')
                                            ->label('名称'),
                                        TextEntry::make('category.name')
                                            ->label('分类'),
                                    ]),
                                    Group::make([
                                        TextEntry::make('sn')
                                            ->label('序列号'),
                                        TextEntry::make('brand.name')
                                            ->label('品牌'),
                                        TextEntry::make('specification')
                                            ->label('规格'),
                                    ]),
                                ]),
                        ]),
                    ]),
            ])->columnSpan(['lg' => 2]),
            Group::make()->schema([
                Section::make()
                    ->schema([
                        ImageEntry::make('image')
                            ->disk('public')
                            ->label('照片'),
                    ]),
            ])->columnSpan(['lg' => 1]),
        ])->columns(3);
    }

    public static function getRelations(): array
    {
        return [
            HasUserRelationManager::class,
            HasPartRelationManager::class,
            HasSoftwareRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Index::route('/'),
            'create' => Create::route('/create'),
            'edit' => Edit::route('/{record}/edit'),
            'view' => View::route('/{record}'),
        ];
    }

    public static function canCreate(): bool
    {
        return false;
    }

    public static function canDelete(Model $record): bool
    {
        return false;
    }
}
