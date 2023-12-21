<?php

namespace App\Filament\Forms;

use Awcodes\Shout\Components\Shout;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;

class SettingForm
{
    /**
     * 创建或编辑资产编号规则的表单.
     */
    public static function createOrEdit(): array
    {
        $description = '例如：PC-{year}{month}{day}-{auto-increment} ，自增长度5，实际上生成的结果为：PC-20230921-00001 。';

        return [
            TextInput::make('name')
                ->label(__('cat.name'))
                ->required(),
            Textarea::make('formula')
                ->label(__('cat.formula'))
                ->required(),
            TextInput::make('auto_increment_length')
                ->label(__('cat.auto_increment_length'))
                ->numeric()
                ->required(),
            Shout::make('description')
                ->label(__('cat.description'))
                ->content($description),
        ];
    }
}
